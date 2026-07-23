<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Official extends Model
{
    protected $fillable = [
        'team_id',
        'club_id',
        'name',
        'role',
        'nationality',
        'ic_number',
        'ic_photo',
        'contact_phone',
        'photo',
        'certificate',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The master club that owns this official — shared across every
     * competition entry of the club.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
