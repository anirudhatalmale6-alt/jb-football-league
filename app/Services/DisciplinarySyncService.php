<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\DisciplinaryFine;
use App\Models\MatchEvent;
use App\Models\MatchGame;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the Disciplinary Fines table in sync with the Yellow/Red card events
 * recorded in Match Events. Every yellow/red card automatically produces the
 * correct fine + 1-match suspension, applying the JBFA competition rules:
 *
 *   - Liga Super (id 2):            3 yellow cards in different matches = RM50
 *   - Liga Perdana / Divisyen:      2 yellow cards in different matches = RM50
 *   - Second yellow -> red (same match):                                 RM100
 *   - Direct red card:                                                   RM150
 *
 * The whole computation is idempotent: fines are keyed by `auto_key`, so
 * syncing the same data twice never creates duplicates, and editing/deleting a
 * card event re-derives the correct set of fines. Manually issued fines
 * (source = 'manual') are never touched.
 */
class DisciplinarySyncService
{
    public const AMT_YELLOW_ACCUM = 50.00;
    public const AMT_RED_SECOND_YELLOW = 100.00;
    public const AMT_RED_DIRECT = 150.00;

    /**
     * Recompute auto fines for every player/competition that has card events,
     * plus any that currently hold auto fines (so removed cards are cleaned).
     * Used by the backfill command and the admin "Sync now" button.
     *
     * @return int number of (player, competition) pairs processed
     */
    public function syncAll(): int
    {
        $pairs = [];

        DB::table('match_events')
            ->join('match_games', 'match_events.match_game_id', '=', 'match_games.id')
            ->whereIn('match_events.event_type', ['yellow_card', 'red_card'])
            ->whereNotNull('match_events.player_id')
            ->select('match_events.player_id', 'match_games.competition_id')
            ->distinct()
            ->get()
            ->each(function ($row) use (&$pairs) {
                $pairs[$row->player_id . ':' . $row->competition_id] = [$row->player_id, $row->competition_id];
            });

        // Also revisit players that already have auto fines but may no longer
        // have any card events (their stale fines must be removed).
        DB::table('disciplinary_fines')
            ->where('source', 'auto')
            ->whereNotNull('player_id')
            ->select('player_id', 'competition_id')
            ->distinct()
            ->get()
            ->each(function ($row) use (&$pairs) {
                $pairs[$row->player_id . ':' . $row->competition_id] = [$row->player_id, $row->competition_id];
            });

        foreach ($pairs as [$playerId, $competitionId]) {
            $this->syncPlayerCompetition((int) $playerId, (int) $competitionId);
        }

        return count($pairs);
    }

    /**
     * Recompute auto fines for every player who has a card event in this match.
     */
    public function syncMatch(MatchGame $match): void
    {
        $playerIds = MatchEvent::where('match_game_id', $match->id)
            ->whereIn('event_type', ['yellow_card', 'red_card'])
            ->whereNotNull('player_id')
            ->distinct()
            ->pluck('player_id');

        foreach ($playerIds as $playerId) {
            $this->syncPlayerCompetition((int) $playerId, (int) $match->competition_id);
        }
    }

    /**
     * The heart of the sync: fully rebuild this player's auto fines within one
     * competition from their card events, then reconcile against what exists.
     */
    public function syncPlayerCompetition(int $playerId, int $competitionId): void
    {
        $competition = Competition::find($competitionId);
        $player = Player::find($playerId);
        if (!$competition || !$player) {
            return;
        }

        // All yellow/red cards for this player in this competition, oldest match
        // first, then by minute within a match.
        $events = DB::table('match_events')
            ->join('match_games', 'match_events.match_game_id', '=', 'match_games.id')
            ->where('match_events.player_id', $playerId)
            ->where('match_games.competition_id', $competitionId)
            ->whereIn('match_events.event_type', ['yellow_card', 'red_card'])
            ->orderBy('match_games.match_date')
            ->orderBy('match_events.minute')
            ->select(
                'match_events.id as event_id',
                'match_events.match_game_id',
                'match_events.event_type',
                'match_events.minute',
                'match_events.team_id'
            )
            ->get();

        // Group cards per match while keeping match chronological order.
        $byMatch = [];
        $order = [];
        foreach ($events as $e) {
            $mid = (int) $e->match_game_id;
            if (!isset($byMatch[$mid])) {
                $byMatch[$mid] = ['team_id' => (int) $e->team_id, 'yellows' => [], 'reds' => []];
                $order[] = $mid;
            }
            if ($e->event_type === 'yellow_card') {
                $byMatch[$mid]['yellows'][] = ['minute' => (int) $e->minute, 'event_id' => (int) $e->event_id];
            } else {
                $byMatch[$mid]['reds'][] = ['minute' => (int) $e->minute, 'event_id' => (int) $e->event_id];
            }
        }

        $threshold = ((int) $competition->id === 2) ? 3 : 2; // Liga Super needs 3, others 2
        $doAccum = ($competition->type === 'league');
        $countingYellows = 0;

        $desired = [];

        foreach ($order as $mid) {
            $m = $byMatch[$mid];
            $teamId = $m['team_id'];
            usort($m['yellows'], fn ($a, $b) => $a['minute'] <=> $b['minute']);
            $nY = count($m['yellows']);
            $nR = count($m['reds']);

            if ($nY >= 2) {
                // Two yellows in the same match -> sending off. RM100.
                // These yellows do NOT feed the across-match accumulation.
                $second = $m['yellows'][1];
                $key = "m{$mid}:p{$playerId}:red2y";
                $desired[$key] = $this->fineAttrs(
                    $playerId, $teamId, $competitionId, $mid,
                    'red_second_yellow', self::AMT_RED_SECOND_YELLOW,
                    'second_yellow', $second['minute'], $second['event_id'],
                    'Kad merah (kad kuning kedua) / Red card from second yellow'
                );
            } elseif ($nR >= 1) {
                // A red without two yellows = direct red. RM150.
                $red = $m['reds'][0];
                $key = "m{$mid}:p{$playerId}:redd";
                $desired[$key] = $this->fineAttrs(
                    $playerId, $teamId, $competitionId, $mid,
                    'red_direct', self::AMT_RED_DIRECT,
                    'red_card', $red['minute'], $red['event_id'],
                    'Kad merah terus / Direct red card'
                );
                // A lone caution alongside a direct red still counts.
                if ($nY === 1) {
                    $this->maybeAccumulate($desired, $doAccum, $threshold, $countingYellows, $playerId, $teamId, $competitionId, $mid, $m['yellows'][0]);
                }
            } elseif ($nY === 1) {
                // Single caution: may trigger an accumulation milestone.
                $this->maybeAccumulate($desired, $doAccum, $threshold, $countingYellows, $playerId, $teamId, $competitionId, $mid, $m['yellows'][0]);
            }
        }

        $this->reconcile($playerId, $competitionId, $desired);
        $this->refreshPlayerSuspension($playerId);
    }

    /**
     * Increment the caution counter and, when it hits a multiple of the
     * threshold, add a yellow-accumulation fine tied to the triggering match.
     */
    private function maybeAccumulate(array &$desired, bool $doAccum, int $threshold, int &$countingYellows, int $playerId, int $teamId, int $competitionId, int $mid, array $yellow): void
    {
        $countingYellows++;
        if (!$doAccum || $countingYellows % $threshold !== 0) {
            return;
        }

        $key = "accum:p{$playerId}:c{$competitionId}:n{$countingYellows}";
        $desc = $threshold === 3
            ? 'Pengumpulan 3 kad kuning (Liga Super) / 3 yellow-card accumulation'
            : 'Pengumpulan 2 kad kuning / 2 yellow-card accumulation';

        $desired[$key] = $this->fineAttrs(
            $playerId, $teamId, $competitionId, $mid,
            'yellow_accumulation', self::AMT_YELLOW_ACCUM,
            'yellow_card', $yellow['minute'], $yellow['event_id'], $desc
        );
    }

    /**
     * Upsert the desired auto fines and remove/neutralise stale ones, without
     * disturbing payment or lifted-suspension state that admins have set.
     */
    private function reconcile(int $playerId, int $competitionId, array $desired): void
    {
        DB::transaction(function () use ($playerId, $competitionId, $desired) {
            $existing = DisciplinaryFine::where('source', 'auto')
                ->where('player_id', $playerId)
                ->where('competition_id', $competitionId)
                ->get()
                ->keyBy('auto_key');

            foreach ($desired as $key => $attrs) {
                $fine = $existing->get($key);
                if ($fine) {
                    $update = [
                        'team_id' => $attrs['team_id'],
                        'match_game_id' => $attrs['match_game_id'],
                        'source_event_id' => $attrs['source_event_id'],
                        'card_type' => $attrs['card_type'],
                        'card_minute' => $attrs['card_minute'],
                        'description' => $attrs['description'],
                    ];
                    // Never rewrite the amount/type of an already-paid fine.
                    if ($fine->status !== 'paid') {
                        $update['fine_type'] = $attrs['fine_type'];
                        $update['amount'] = $attrs['amount'];
                    }
                    // Leave a manually lifted suspension lifted.
                    if (!$fine->suspension_lifted_at) {
                        $update['is_suspended'] = $attrs['is_suspended'];
                        $update['suspension_type'] = $attrs['suspension_type'];
                        $update['suspension_matches'] = $attrs['suspension_matches'];
                    }
                    $fine->update($update);
                    $existing->forget($key);
                } else {
                    DisciplinaryFine::create(array_merge($attrs, ['auto_key' => $key]));
                }
            }

            // Anything still in $existing no longer has a matching card.
            foreach ($existing as $stale) {
                if ($stale->status === 'paid') {
                    // Preserve the paid record for audit; just drop the suspension.
                    $stale->update([
                        'is_suspended' => false,
                        'notes' => trim(($stale->notes ? $stale->notes . "\n" : '') . '[Auto] Source card event no longer exists.'),
                    ]);
                } else {
                    $stale->delete();
                }
            }
        });
    }

    /**
     * Mirror the player's suspended flag to their active auto/manual bans, using
     * the same rule the line-up screen uses (is_suspended && not lifted).
     */
    private function refreshPlayerSuspension(int $playerId): void
    {
        $player = Player::find($playerId);
        if (!$player) {
            return;
        }

        $hasActive = DisciplinaryFine::where('player_id', $playerId)
            ->where('is_suspended', true)
            ->whereNull('suspension_lifted_at')
            ->exists();

        if ($hasActive && $player->status !== 'suspended') {
            $player->update(['status' => 'suspended']);
        } elseif (!$hasActive && $player->status === 'suspended') {
            $player->update(['status' => 'active']);
        }
    }

    private function fineAttrs(int $playerId, int $teamId, int $competitionId, int $matchId, string $fineType, float $amount, string $cardType, int $minute, int $eventId, string $desc): array
    {
        return [
            'team_id' => $teamId,
            'player_id' => $playerId,
            'competition_id' => $competitionId,
            'match_game_id' => $matchId,
            'issued_by' => null,
            'source' => 'auto',
            'source_event_id' => $eventId,
            'fine_type' => $fineType,
            'card_type' => $cardType,
            'card_minute' => $minute,
            'description' => $desc,
            'amount' => $amount,
            'currency' => 'MYR',
            'status' => 'pending',
            'is_suspended' => true,
            'suspension_type' => 'match_ban',
            'suspension_matches' => 1,
            'matches_served' => 0,
        ];
    }
}
