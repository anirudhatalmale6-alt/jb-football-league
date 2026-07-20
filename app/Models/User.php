<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'club_team',
        'name',
        'email',
        'password',
        'role',
        'team_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    private ?array $cachedManagedTeamIds = null;

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = mb_strtoupper(trim($value));
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = mb_strtolower($value);
    }

    public function setClubTeamAttribute($value): void
    {
        $this->attributes['club_team'] = mb_strtoupper(trim($value));
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function managedTeams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')->withTimestamps();
    }

    public function managedTeamIds(): array
    {
        if ($this->cachedManagedTeamIds === null) {
            $this->cachedManagedTeamIds = $this->managedTeams()->pluck('teams.id')->map(fn($id) => (int)$id)->toArray();
        }
        return $this->cachedManagedTeamIds;
    }

    public function managesTeam($teamId): bool
    {
        return in_array((int)$teamId, $this->managedTeamIds());
    }

    public function hasTeams(): bool
    {
        return !empty($this->managedTeamIds());
    }

    public function isSuper(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isLeagueAdmin(): bool
    {
        return $this->role === 'league_admin';
    }

    public function isTeamManager(): bool
    {
        return $this->role === 'team_manager';
    }

    public function isHeadMatchCommissioner(): bool
    {
        return $this->role === 'head_match_commissioner';
    }

    public function isMatchCommissioner(): bool
    {
        return $this->role === 'match_commissioner';
    }

    /** Anyone who runs match-day operations (admins + commissioners). */
    public function canOperateMatches(): bool
    {
        return $this->isSuper() || $this->isLeagueAdmin()
            || $this->isHeadMatchCommissioner() || $this->isMatchCommissioner();
    }

    /** Who may view and manage the disciplinary fines panel. */
    public function canManageDiscipline(): bool
    {
        return $this->isSuper() || $this->isLeagueAdmin() || $this->isHeadMatchCommissioner();
    }

    /** Send the branded JBFA password-reset email instead of Laravel's default. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
