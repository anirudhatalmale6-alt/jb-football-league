<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    protected $table = 'match_events';

    protected $fillable = [
        'match_game_id',
        'team_id',
        'player_id',
        'event_type',
        'minute',
        'extra_time_minute',
        'related_player_id',
        'notes',
    ];

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
