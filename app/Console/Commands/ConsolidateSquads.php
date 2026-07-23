<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Official;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time data migration to the master-club squad model.
 *
 *   1. Create a `clubs` row per distinct club (by the app's existing
 *      case-insensitive/trimmed name convention) and stamp club_id on every
 *      teams / players / officials row.
 *   2. Collapse the duplicate player & official rows that the old
 *      one-list-per-competition model produced, so each club has exactly ONE
 *      squad. Every match reference to a duplicated player is re-pointed at the
 *      surviving canonical row BEFORE the duplicate is deleted, so no match
 *      history is lost.
 *
 * Player rows are only merged when they are provably the same person: identical
 * IC number, or (when IC is missing) identical name + date of birth. Anything
 * ambiguous is left untouched and reported.
 *
 * Run with --dry-run first: everything executes inside a transaction and is
 * rolled back, printing exactly what a real run would change.
 */
class ConsolidateSquads extends Command
{
    protected $signature = 'squad:consolidate {--dry-run : Show what would change without persisting}';

    protected $description = 'Consolidate per-competition squads into shared master-club squads';

    /** Player-id columns that must be re-pointed when a duplicate is merged. */
    private const PLAYER_REFS = [
        ['table' => 'match_lineups',          'column' => 'player_id'],
        ['table' => 'match_events',           'column' => 'player_id'],
        ['table' => 'match_events',           'column' => 'related_player_id'],
        ['table' => 'disciplinary_fines',     'column' => 'player_id'],
        ['table' => 'substitution_requests',  'column' => 'player_out_id'],
        ['table' => 'substitution_requests',  'column' => 'player_in_id'],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->info($dry
            ? '=== DRY RUN — no changes will be saved ==='
            : '=== LIVE RUN — changes WILL be saved ===');

        DB::beginTransaction();

        try {
            $report = [
                'clubs_created'     => 0,
                'teams_stamped'     => 0,
                'players_before'    => Player::count(),
                'officials_before'  => Official::count(),
                'players_merged'    => 0,
                'officials_merged'  => 0,
                'refs_repointed'    => 0,
                'unmerged_players'  => [],
                'club_lines'        => [],
            ];

            $this->backfillClubs($report);
            $this->stampChildRecords();
            $this->dedupe($report);

            $report['players_after']   = Player::count();
            $report['officials_after'] = Official::count();

            $this->printReport($report, $dry);

            if ($dry) {
                DB::rollBack();
                $this->warn('Dry run complete — transaction rolled back. Nothing was saved.');
            } else {
                DB::commit();
                $this->info('Consolidation committed successfully.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Aborted, transaction rolled back: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Create a club per distinct (case-insensitive, trimmed) team name and
     * stamp club_id on every team row — including soft-deleted ones, whose
     * players still need an owner.
     */
    private function backfillClubs(array &$report): void
    {
        $teams = Team::withTrashed()->orderBy('id')->get();

        $clubsByKey = [];

        foreach ($teams as $team) {
            $key = mb_strtolower(trim($team->name));

            if (!isset($clubsByKey[$key])) {
                $existing = Club::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if ($existing) {
                    $clubsByKey[$key] = $existing;
                } else {
                    $clubsByKey[$key] = Club::create([
                        'name'       => $team->name,
                        'short_name' => $team->short_name,
                        'logo'       => $team->logo,
                    ]);
                    $report['clubs_created']++;
                }
            }

            $club = $clubsByKey[$key];

            if ((int) $team->club_id !== (int) $club->id) {
                $team->club_id = $club->id;
                $team->saveQuietly();
                $report['teams_stamped']++;
            }
        }
    }

    /**
     * Copy each team's club_id down onto its players and officials.
     */
    private function stampChildRecords(): void
    {
        DB::statement('
            UPDATE players p
            JOIN teams t ON t.id = p.team_id
            SET p.club_id = t.club_id
            WHERE p.club_id IS NULL OR p.club_id <> t.club_id
        ');

        DB::statement('
            UPDATE officials o
            JOIN teams t ON t.id = o.team_id
            SET o.club_id = t.club_id
            WHERE o.club_id IS NULL OR o.club_id <> t.club_id
        ');
    }

    private function dedupe(array &$report): void
    {
        $clubs = Club::with(['teams' => fn ($q) => $q->withTrashed()])->orderBy('name')->get();

        foreach ($clubs as $club) {
            $playersBefore   = Player::where('club_id', $club->id)->count();
            $officialsBefore = Official::where('club_id', $club->id)->count();

            $pMerged = $this->dedupePlayers($club, $report);
            $oMerged = $this->dedupeOfficials($club);

            $report['players_merged']   += $pMerged;
            $report['officials_merged'] += $oMerged;

            if ($pMerged || $oMerged || $club->teams->count() > 1) {
                $report['club_lines'][] = sprintf(
                    '%-52s comps:%d  players:%d->%d  officials:%d->%d',
                    mb_strimwidth($club->name, 0, 52),
                    $club->teams->count(),
                    $playersBefore,
                    $playersBefore - $pMerged,
                    $officialsBefore,
                    $officialsBefore - $oMerged,
                );
            }
        }
    }

    /**
     * Merge duplicate players within a club. Returns the number of duplicate
     * rows deleted.
     */
    private function dedupePlayers(Club $club, array &$report): int
    {
        $players = Player::where('club_id', $club->id)->orderBy('id')->get();

        $groups = [];
        foreach ($players as $player) {
            $key = $this->playerKey($player);
            if ($key === null) {
                $report['unmerged_players'][] = $club->name . ' :: ' . $player->name . ' (id ' . $player->id . ', no IC/DOB)';
                continue;
            }
            $groups[$key][] = $player;
        }

        $merged = 0;

        foreach ($groups as $group) {
            if (count($group) < 2) {
                continue;
            }

            $canonical = $this->pickCanonical($group);

            foreach ($group as $dup) {
                if ($dup->id === $canonical->id) {
                    continue;
                }

                foreach (self::PLAYER_REFS as $ref) {
                    $report['refs_repointed'] += DB::table($ref['table'])
                        ->where($ref['column'], $dup->id)
                        ->update([$ref['column'] => $canonical->id]);
                }

                $dup->delete();
                $merged++;
            }
        }

        return $merged;
    }

    /**
     * Officials have no inbound references, so merging is a straight delete of
     * the redundant rows. Returns the number deleted.
     */
    private function dedupeOfficials(Club $club): int
    {
        $officials = Official::where('club_id', $club->id)->orderBy('id')->get();

        $groups = [];
        foreach ($officials as $official) {
            $key = $this->officialKey($official);
            if ($key === null) {
                continue;
            }
            $groups[$key][] = $official;
        }

        $merged = 0;

        foreach ($groups as $group) {
            if (count($group) < 2) {
                continue;
            }

            $canonical = $this->pickCanonical($group);

            foreach ($group as $dup) {
                if ($dup->id === $canonical->id) {
                    continue;
                }
                $dup->delete();
                $merged++;
            }
        }

        return $merged;
    }

    /**
     * A player's identity key: cleaned IC if present, else name + DOB. Returns
     * null when neither is available (never merged — left for a human to check).
     */
    private function playerKey(Player $player): ?string
    {
        $ic = preg_replace('/[^0-9]/', '', (string) $player->ic_number);
        if ($ic !== '') {
            return 'ic:' . $ic;
        }

        $dob = $player->getRawOriginal('date_of_birth');
        if ($player->name && $dob) {
            return 'nd:' . mb_strtolower(trim($player->name)) . '|' . $dob;
        }

        return null;
    }

    /**
     * An official's identity key: cleaned IC if present, else name + role.
     */
    private function officialKey(Official $official): ?string
    {
        $ic = preg_replace('/[^0-9]/', '', (string) $official->ic_number);
        if ($ic !== '') {
            return 'ic:' . $ic;
        }

        if ($official->name && $official->role) {
            return 'nr:' . mb_strtolower(trim($official->name)) . '|' . mb_strtolower(trim($official->role));
        }

        return null;
    }

    /**
     * Keep the row with the most complete data (photos, IC, DOB); tie-break on
     * the lowest id so the choice is deterministic.
     */
    private function pickCanonical(array $group): object
    {
        usort($group, function ($a, $b) {
            $score = $this->completeness($b) <=> $this->completeness($a);
            return $score !== 0 ? $score : ($a->id <=> $b->id);
        });

        return $group[0];
    }

    private function completeness(object $row): int
    {
        $fields = ['ic_number', 'ic_photo', 'photo', 'bg_removed_photo', 'date_of_birth', 'certificate'];
        $score = 0;
        foreach ($fields as $f) {
            if (!empty($row->getAttribute($f))) {
                $score++;
            }
        }
        return $score;
    }

    private function printReport(array $report, bool $dry): void
    {
        $this->newLine();
        $this->line('---- Per-club summary (only multi-competition / changed clubs) ----');
        foreach ($report['club_lines'] as $line) {
            $this->line($line);
        }

        $this->newLine();
        $this->line('---- Totals ----');
        $this->line('Clubs created         : ' . $report['clubs_created']);
        $this->line('Team rows stamped     : ' . $report['teams_stamped']);
        $this->line('Players  : ' . $report['players_before'] . ' -> ' . $report['players_after']
            . '  (merged ' . $report['players_merged'] . ')');
        $this->line('Officials: ' . $report['officials_before'] . ' -> ' . $report['officials_after']
            . '  (merged ' . $report['officials_merged'] . ')');
        $this->line('Match references re-pointed: ' . $report['refs_repointed']);

        if (!empty($report['unmerged_players'])) {
            $this->newLine();
            $this->warn('Players left UN-merged (no IC and no DOB — verify manually):');
            foreach ($report['unmerged_players'] as $u) {
                $this->line('  - ' . $u);
            }
        }
    }
}
