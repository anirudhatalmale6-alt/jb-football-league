<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnockoutMatch extends Model
{
    protected $fillable = [
        'competition_id',
        'round',
        'position',
        'home_team_id',
        'away_team_id',
        'match_game_id',
        'winner_team_id',
        'home_penalty_score',
        'away_penalty_score',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public static function roundLabel(string $round): string
    {
        return match($round) {
            'round_of_16' => 'Round of 16',
            'quarter_final' => 'Quarter Final',
            'semi_final' => 'Semi Final',
            'final' => 'Final',
            default => ucfirst(str_replace('_', ' ', $round)),
        };
    }

    public static function roundLabelMs(string $round): string
    {
        return match($round) {
            'round_of_16' => 'Pusingan 16',
            'quarter_final' => 'Suku Akhir',
            'semi_final' => 'Separuh Akhir',
            'final' => 'Akhir',
            default => ucfirst(str_replace('_', ' ', $round)),
        };
    }

    public static function roundOrder(string $round): int
    {
        return match($round) {
            'round_of_16' => 1,
            'quarter_final' => 2,
            'semi_final' => 3,
            'final' => 4,
            default => 99,
        };
    }

    public static function nextRound(string $round): ?string
    {
        return match($round) {
            'round_of_16' => 'quarter_final',
            'quarter_final' => 'semi_final',
            'semi_final' => 'final',
            default => null,
        };
    }

    public static function nextPosition(int $position): int
    {
        return (int) ceil($position / 2);
    }

    public static function isHomeInNext(int $position): bool
    {
        return $position % 2 === 1;
    }
}
