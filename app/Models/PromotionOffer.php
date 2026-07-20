<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionOffer extends Model
{
    protected $fillable = [
        'team_id',
        'type',
        'from_competition_id',
        'to_competition_id',
        'offered_by',
        'status',
        'accept_only',
        'venue_name',
        'venue_address',
        'coaching_license',
        'fee_agreed',
        'offered_at',
        'expires_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'fee_agreed' => 'boolean',
            'accept_only' => 'boolean',
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function fromCompetition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'from_competition_id');
    }

    public function toCompetition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'to_competition_id');
    }

    public function offeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offered_by');
    }

    public function isExpired(): bool
    {
        return $this->status === 'pending' && now()->isAfter($this->expires_at);
    }

    public function isPromotion(): bool
    {
        return $this->type === 'promotion';
    }

    public function isRelegation(): bool
    {
        return $this->type === 'relegation';
    }
}
