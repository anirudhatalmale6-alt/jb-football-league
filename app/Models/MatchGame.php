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
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
        ];
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

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }
}
