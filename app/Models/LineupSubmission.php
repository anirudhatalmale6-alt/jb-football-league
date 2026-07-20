<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LineupSubmission extends Model
{
    protected $table = 'lineup_submissions';

    protected $fillable = [
        'match_game_id',
        'team_id',
        'submitted_by',
        'status',
        'rejection_reason',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'locked_at' => 'datetime',
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

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(MatchLineup::class, 'lineup_submission_id');
    }

    public function startingEleven(): HasMany
    {
        return $this->hasMany(MatchLineup::class, 'lineup_submission_id')->where('is_starting', true);
    }

    public function substitutes(): HasMany
    {
        return $this->hasMany(MatchLineup::class, 'lineup_submission_id')->where('is_starting', false);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canSubmit(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }
}
