<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = [
        'name',
        'season',
        'type',
        'status',
        'start_date',
        'end_date',
        'description',
        'max_players',
        'max_officials',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function matchGames(): HasMany
    {
        return $this->hasMany(MatchGame::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
