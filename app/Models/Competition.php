<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = [
        'name',
        'code_prefix',
        'season',
        'type',
        'status',
        'match_duration',
        'start_date',
        'end_date',
        'description',
        'registration_fee',
        'security_deposit',
        'matchday_fee',
        'payment_url',
        'logo',
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

    public function knockoutMatches(): HasMany
    {
        return $this->hasMany(KnockoutMatch::class);
    }

    /**
     * Total registration fee = registration + deposit + matchday + RM50 annual (leagues only).
     * Mirrors the calculation used in RegistrationController and the payment receipt.
     */
    public function totalFee(): float
    {
        $annual = ($this->type === 'league') ? 50.00 : 0.00;

        return (float) $this->registration_fee
            + (float) $this->security_deposit
            + (float) $this->matchday_fee
            + $annual;
    }

    /**
     * Base fee only (registration + deposit + matchday), WITHOUT the RM50
     * annual affiliate fee. Whether a team also owes the RM50 is decided
     * per team via Team::owesAffiliateFee().
     */
    public function baseFee(): float
    {
        return (float) $this->registration_fee
            + (float) $this->security_deposit
            + (float) $this->matchday_fee;
    }

    /**
     * Malay league name used on letters, banners and emails.
     */
    public function malayName(): string
    {
        return match ((int) $this->id) {
            2 => 'Liga Super JBFA',
            3 => 'Liga Perdana JBFA',
            4 => 'Liga Divisyen JBFA',
            default => $this->name,
        };
    }
}
