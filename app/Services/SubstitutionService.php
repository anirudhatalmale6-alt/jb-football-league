<?php

namespace App\Services;

use App\Models\MatchEvent;
use App\Models\MatchGame;
use App\Models\MatchLineup;
use App\Models\Player;
use Illuminate\Support\Collection;

/**
 * Works out who is currently on the field vs on the bench for a team in a
 * match, by starting from the approved starting XI and applying substitution
 * events (both MC-manual and Team-Manager-requested) plus red cards. Used for
 * the substitution request form and the U23 on-field rule.
 */
class SubstitutionService
{
    /** Player ids in the team's starting XI. */
    private function starterIds(MatchGame $match, int $teamId): Collection
    {
        return MatchLineup::where('match_game_id', $match->id)
            ->where('team_id', $teamId)
            ->where('is_starting', true)
            ->pluck('player_id');
    }

    /** Player ids named as substitutes in the team's line-up. */
    private function benchNamedIds(MatchGame $match, int $teamId): Collection
    {
        return MatchLineup::where('match_game_id', $match->id)
            ->where('team_id', $teamId)
            ->where('is_starting', false)
            ->pluck('player_id');
    }

    /** [outIds, inIds] from all substitution events for this team. */
    private function subMovements(MatchGame $match, int $teamId): array
    {
        $subs = MatchEvent::where('match_game_id', $match->id)
            ->where('team_id', $teamId)
            ->where('event_type', 'substitution')
            ->get();

        return [
            $subs->pluck('player_id')->filter()->values(),         // went off
            $subs->pluck('related_player_id')->filter()->values(), // came on
        ];
    }

    /** Player ids sent off (red card) for this team. */
    private function sentOffIds(MatchGame $match, int $teamId): Collection
    {
        return MatchEvent::where('match_game_id', $match->id)
            ->where('team_id', $teamId)
            ->where('event_type', 'red_card')
            ->pluck('player_id')
            ->filter()
            ->values();
    }

    /** Ids currently on the field = (starters + came-on) - went-off - sent-off. */
    public function onFieldIds(MatchGame $match, int $teamId): Collection
    {
        [$outs, $ins] = $this->subMovements($match, $teamId);
        $sentOff = $this->sentOffIds($match, $teamId);

        return $this->starterIds($match, $teamId)
            ->merge($ins)
            ->reject(fn ($id) => $outs->contains($id) || $sentOff->contains($id))
            ->unique()
            ->values();
    }

    /** Ids available to come on = named subs not already used and not sent off. */
    public function benchIds(MatchGame $match, int $teamId): Collection
    {
        [, $ins] = $this->subMovements($match, $teamId);
        $sentOff = $this->sentOffIds($match, $teamId);

        return $this->benchNamedIds($match, $teamId)
            ->reject(fn ($id) => $ins->contains($id) || $sentOff->contains($id))
            ->unique()
            ->values();
    }

    /** On-field players (Player models), ordered by jersey number. */
    public function onFieldPlayers(MatchGame $match, int $teamId): Collection
    {
        return Player::whereIn('id', $this->onFieldIds($match, $teamId))
            ->orderBy('jersey_number')
            ->get();
    }

    /** Bench players (Player models), ordered by jersey number. */
    public function benchPlayers(MatchGame $match, int $teamId): Collection
    {
        return Player::whereIn('id', $this->benchIds($match, $teamId))
            ->orderBy('jersey_number')
            ->get();
    }

    /** Numeric current match minute from the live clock (0 if not running). */
    public function currentMinute(MatchGame $match): int
    {
        $raw = $match->match_minute; // "56'", "HT", "FT", or null
        if (is_string($raw) && preg_match('/(\d+)/', $raw, $m)) {
            return (int) $m[1];
        }
        if ($raw === 'HT') {
            return $match->halfDuration();
        }
        return 0;
    }

    /**
     * Would swapping playerOut for playerIn break the "at least 1 U23 on the
     * field at all times" rule? Only blocks if the team currently satisfies the
     * rule and the swap would drop it below 1 (never blocks an already-broken
     * state so a match with bad data isn't frozen).
     */
    public function wouldViolateU23(MatchGame $match, int $teamId, Player $playerOut, Player $playerIn): bool
    {
        $onField = Player::whereIn('id', $this->onFieldIds($match, $teamId))->get();
        $currentU23 = $onField->filter(fn ($p) => $p->is_u23)->count();

        if ($currentU23 < 1) {
            return false;
        }

        $projected = $currentU23
            - ($playerOut->is_u23 ? 1 : 0)
            + ($playerIn->is_u23 ? 1 : 0);

        return $projected < 1;
    }
}
