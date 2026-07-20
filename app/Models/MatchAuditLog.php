<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchAuditLog extends Model
{
    protected $table = 'match_audit_logs';

    protected $fillable = [
        'match_game_id',
        'action',
        'match_code',
        'home_team',
        'away_team',
        'competition',
        'match_date',
        'status_at_action',
        'reason',
        'performed_by_user_id',
        'performed_by_name',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
        ];
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    /**
     * Snapshot a match into an audit-log record for the given action.
     * Match details are copied as plain text so the log stays meaningful
     * even after the match is permanently deleted.
     */
    public static function record(MatchGame $match, string $action, ?string $reason, ?User $user): self
    {
        return static::create([
            'match_game_id' => $match->id,
            'action' => $action,
            'match_code' => $match->match_code,
            'home_team' => optional($match->homeTeam)->name,
            'away_team' => optional($match->awayTeam)->name,
            'competition' => optional($match->competition)->name,
            'match_date' => $match->match_date,
            'status_at_action' => $match->status,
            'reason' => $reason,
            'performed_by_user_id' => $user?->id,
            'performed_by_name' => $user?->name,
        ]);
    }
}
