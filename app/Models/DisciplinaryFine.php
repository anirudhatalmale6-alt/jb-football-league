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
        'proof_file',
        'transaction_id',
        'paid_at',
        'notes',
        'is_suspended',
        'suspension_type',
        'suspension_matches',
        'matches_served',
        'suspension_lifted_at',
        'suspension_lifted_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'is_suspended' => 'boolean',
            'suspension_lifted_at' => 'datetime',
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

    public function suspensionBadge(): string
    {
        if (!$this->is_suspended) {
            return '';
        }

        if ($this->suspension_lifted_at) {
            return '<span class="badge bg-success"><i class="fas fa-unlock me-1"></i>Dibebaskan / Lifted</span>';
        }

        if ($this->suspension_type === 'match_ban') {
            return '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Digantung ' . $this->matches_served . '/' . $this->suspension_matches . ' perlawanan</span>';
        }

        return '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Digantung / Suspended</span>';
    }

    public function isSuspensionActive(): bool
    {
        return $this->is_suspended && !$this->suspension_lifted_at;
    }
}
