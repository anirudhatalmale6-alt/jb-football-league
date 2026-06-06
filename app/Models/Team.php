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
        'status',
    ];

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
}
