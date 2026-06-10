<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'competition_id',
        'group_id',
        'name',
        'short_name',
        'logo',
        'manager_name',
        'contact_email',
        'contact_phone',
        'applicant_name',
        'applicant_position',
        'venue_name',
        'venue_location',
        'venue_coordinator_name',
        'venue_coordinator_phone',
        'status',
        'terms_agreed',
        'terms_agreed_at',
        'terms_agreed_by',
    ];

    protected function casts(): array
    {
        return [
            'terms_agreed' => 'boolean',
            'terms_agreed_at' => 'datetime',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function officials(): HasMany
    {
        return $this->hasMany(Official::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'away_team_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }

    public function registrationPayments(): HasMany
    {
        return $this->hasMany(RegistrationPayment::class);
    }
}
