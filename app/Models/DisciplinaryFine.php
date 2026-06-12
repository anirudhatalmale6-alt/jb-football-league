<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryFine extends Model
{
    protected $fillable = [
        'team_id',
        'player_id',
        'competition_id',
        'match_game_id',
        'issued_by',
        'fine_type',
        'description',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_url',
        'transaction_id',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function fineTypeLabel(): string
    {
        return match ($this->fine_type) {
            'red_card' => __('app.fine_red_card'),
            'yellow_accumulation' => __('app.fine_yellow_accumulation'),
            'misconduct' => __('app.fine_misconduct'),
            'late_arrival' => __('app.fine_late_arrival'),
            'walkover' => __('app.fine_walkover'),
            'other' => __('app.fine_other'),
            default => $this->fine_type,
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'paid' => '<span class="badge bg-success">Dibayar / Paid</span>',
            'pending' => '<span class="badge bg-warning text-dark">Belum Bayar / Pending</span>',
            'waived' => '<span class="badge bg-secondary">Dikecualikan / Waived</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }
}
