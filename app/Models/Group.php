<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'competition_id',
        'order',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }
}
