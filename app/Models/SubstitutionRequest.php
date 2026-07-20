<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubstitutionRequest extends Model
{
    protected $fillable = [
        'match_game_id',
        'team_id',
        'player_out_id',
        'player_in_id',
        'minute',
        'reason',
        'status',
        'requested_by',
        'reviewed_by',
        'rejection_reason',
        'match_event_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'minute' => 'integer',
            'reviewed_at' => 'datetime',
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

    public function playerOut(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_out_id');
    }

    public function playerIn(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_in_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>Pending MC Approval</span>',
            'approved' => '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>',
            'rejected' => '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Rejected</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}
