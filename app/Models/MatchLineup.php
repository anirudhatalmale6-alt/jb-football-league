<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLineup extends Model
{
    protected $table = 'match_lineups';

    protected $fillable = [
        'match_game_id',
        'team_id',
        'player_id',
        'jersey_number',
        'position',
        'is_starting',
    ];

    protected function casts(): array
    {
        return [
            'is_starting' => 'boolean',
        ];
    }

    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
