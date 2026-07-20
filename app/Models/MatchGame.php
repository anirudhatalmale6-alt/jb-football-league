<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchGame extends Model
{
    protected $table = 'match_games';

    protected $fillable = [
        'match_code',
        'competition_id',
        'home_team_id',
        'away_team_id',
        'matchday',
        'match_date',
        'venue',
        'status',
        'home_score',
        'away_score',
        'referee',
        'assistant_referee_1',
        'assistant_referee_2',
        'fourth_official',
        'match_commissioner',
        'notes',
        'live_started_at',
        'half_time_at',
        'second_half_at',
        'full_time_at',
        'closed_at',
        'first_half_stoppage',
        'second_half_stoppage',
        'match_remarks',
        'final_submitted_at',
        'final_submitted_by_user_id',
        'final_minute',
        'assigned_mc_user_id',
        'archived_at',
        'archived_by_user_id',
        'archive_reason',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'live_started_at' => 'datetime',
            'half_time_at' => 'datetime',
            'second_half_at' => 'datetime',
            'full_time_at' => 'datetime',
            'closed_at' => 'datetime',
            'final_submitted_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** True once this match has been archived (hidden from normal listings). */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * A match carrying official data that must not be casually deleted: it is
     * live/played (any status past "scheduled"), or its report has been
     * submitted and locked. Deleting one requires extra confirmation.
     */
    public function isOfficialData(): bool
    {
        if ($this->isLocked()) {
            return true;
        }
        return !in_array($this->status, ['scheduled', 'postponed'], true);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_user_id');
    }

    /** Total match length in minutes (from the competition, default 90). */
    public function matchDuration(): int
    {
        return (int) (optional($this->competition)->match_duration ?? 90);
    }

    /** Half length in minutes (e.g. 45 for a 90-minute match). */
    public function halfDuration(): int
    {
        return (int) ceil($this->matchDuration() / 2);
    }

    /** A locked match: final report submitted, or legacy closed status. */
    public function isLocked(): bool
    {
        return $this->final_submitted_at !== null || $this->status === 'closed';
    }

    /**
     * Who may see the match-day control panel for this match. Admins and the
     * Head MC can operate any match; a plain Match Commissioner only the match
     * assigned to them.
     */
    public function canOperateBy(?User $user): bool
    {
        if (!$user || !$user->canOperateMatches()) {
            return false;
        }
        if ($user->isMatchCommissioner()) {
            return (int) $this->assigned_mc_user_id === (int) $user->id;
        }
        return true;
    }

    /**
     * Who may edit this match right now. Before final submission, any operator
     * who can operate it can edit. After it is locked, only a Super Admin or
     * Head Match Commissioner may amend.
     */
    public function canEditBy(?User $user): bool
    {
        if (!$this->canOperateBy($user)) {
            return false;
        }
        if ($this->isLocked()) {
            return $user->isSuper() || $user->isHeadMatchCommissioner();
        }
        return true;
    }

    public function assignedMc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mc_user_id');
    }

    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(McAssignmentLog::class)->latest();
    }

    protected static function booted(): void
    {
        static::creating(function ($match) {
            if (empty($match->match_code) && $match->competition_id) {
                $competition = Competition::find($match->competition_id);
                if ($competition && $competition->code_prefix) {
                    $count = static::where('competition_id', $match->competition_id)->count() + 1;
                    $match->match_code = $competition->code_prefix . str_pad($count, 2, '0', STR_PAD_LEFT);
                }
            }
        });
    }

    public function isLive(): bool
    {
        return in_array($this->status, ['live', 'second_half']);
    }

    public function isPlaying(): bool
    {
        return in_array($this->status, ['live', 'half_time', 'second_half']);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['full_time', 'completed', 'closed']);
    }

    public function getMatchMinuteAttribute(): ?string
    {
        $half = $this->halfDuration();
        $full = $this->matchDuration();
        if ($this->status === 'live' && $this->live_started_at) {
            $mins = (int) $this->live_started_at->diffInMinutes(now());
            return min($mins, $half + ($this->first_half_stoppage ?? 0)) . "'";
        }
        if ($this->status === 'half_time') {
            return 'HT';
        }
        if ($this->status === 'second_half' && $this->second_half_at) {
            $mins = $half + (int) $this->second_half_at->diffInMinutes(now());
            return min($mins, $full + ($this->second_half_stoppage ?? 0)) . "'";
        }
        if ($this->status === 'full_time' || $this->status === 'completed' || $this->status === 'closed') {
            return 'FT';
        }
        return null;
    }

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

    public function lineups(): HasMany
    {
        return $this->hasMany(MatchLineup::class);
    }

    public function lineupSubmissions(): HasMany
    {
        return $this->hasMany(LineupSubmission::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(MatchSignature::class);
    }

    public function finalSubmittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_submitted_by_user_id');
    }

    public function jerseys(): HasMany
    {
        return $this->hasMany(MatchJersey::class);
    }

    public function matchDayPhotos(): HasMany
    {
        return $this->hasMany(MatchDayPhoto::class);
    }

    public function substitutionRequests(): HasMany
    {
        return $this->hasMany(SubstitutionRequest::class);
    }

    public function homeJersey(): ?MatchJersey
    {
        return $this->jerseys->firstWhere('team_id', $this->home_team_id);
    }

    public function awayJersey(): ?MatchJersey
    {
        return $this->jerseys->firstWhere('team_id', $this->away_team_id);
    }

    public function getSignature(string $role): ?MatchSignature
    {
        return $this->signatures->firstWhere('role', $role);
    }

    public function allSignaturesConfirmed(): bool
    {
        $requiredRoles = ['head_referee', 'home_team_rep', 'away_team_rep', 'match_commissioner'];
        foreach ($requiredRoles as $role) {
            $sig = $this->getSignature($role);
            if (!$sig || !$sig->confirmed) {
                return false;
            }
        }
        return true;
    }
}
