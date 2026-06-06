<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Official extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'role',
        'ic_number',
        'contact_phone',
        'photo',
        'certificate',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
