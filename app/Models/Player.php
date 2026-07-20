<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'jersey_number',
        'position',
        'date_of_birth',
        'nationality',
        'ic_number',
        'ic_photo',
        'photo',
        'status',
        'verification_status',
        'bg_removed_photo',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }


    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = mb_strtoupper(trim($value));
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getAgeAttribute(): ?int
    {
        $dob = $this->getDateOfBirthFromIc();
        if (!$dob) {
            return $this->date_of_birth ? $this->date_of_birth->age : null;
        }
        return $dob->age;
    }

    public function getDateOfBirthFromIc(): ?Carbon
    {
        if (!$this->ic_number) return null;
        $ic = preg_replace('/[^0-9]/', '', $this->ic_number);
        if (strlen($ic) < 6) return null;

        $yy = substr($ic, 0, 2);
        $mm = substr($ic, 2, 2);
        $dd = substr($ic, 4, 2);

        if (!checkdate((int)$mm, (int)$dd, 2000)) return null;

        $year = (2000 + (int)$yy <= (int)date('Y')) ? 2000 + (int)$yy : 1900 + (int)$yy;

        try {
            return Carbon::createFromDate($year, (int)$mm, (int)$dd);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getIsU23Attribute(): bool
    {
        $age = $this->age;
        return $age !== null && $age <= 23;
    }

    public function getAgeBadgeAttribute(): string
    {
        if ($this->is_u23) {
            return '<span class="badge bg-warning text-dark" style="font-size:0.65rem;vertical-align:middle;">U23</span>';
        }
        return '';
    }
}
