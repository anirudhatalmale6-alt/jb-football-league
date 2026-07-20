<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McAssignmentLog extends Model
{
    protected $fillable = [
        'match_game_id',
        'previous_mc_user_id',
        'new_mc_user_id',
        'changed_by_user_id',
        'reason',
    ];

    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    public function previousMc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_mc_user_id');
    }

    public function newMc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_mc_user_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
