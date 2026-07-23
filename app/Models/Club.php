<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A football club — the master profile that owns one squad and one officials
 * list, shared across every competition the club takes part in. Each
 * competition entry is a `teams` row that references this club.
 */
class Club extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'logo',
    ];

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = mb_strtoupper(trim($value));
    }

    /**
     * All competition entries (one per competition the club joined).
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * The club's master squad — registered once, shared everywhere.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * The club's officials — registered once, shared everywhere.
     */
    public function officials(): HasMany
    {
        return $this->hasMany(Official::class);
    }

    /**
     * Resolve (or create) the club for a given team name. Matching mirrors the
     * app's long-standing "same club" convention: case-insensitive, trimmed.
     */
    public static function resolveByName(string $name): self
    {
        $needle = mb_strtolower(trim($name));

        $club = static::whereRaw('LOWER(TRIM(name)) = ?', [$needle])->first();

        return $club ?: static::create(['name' => $name]);
    }
}
