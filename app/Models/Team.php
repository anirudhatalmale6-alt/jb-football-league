<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'competition_id',
        'club_id',
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
        'rejection_reason',
        'terms_agreed',
        'terms_agreed_at',
        'terms_agreed_by',
        'resubmitted_at',
        'affiliate_fee_paid',
        'affiliate_fee_paid_at',
        'affiliate_fee_reference',
        'affiliate_fee_marked_by',
        'affiliate_fee_reminded_at',
        'affiliate_fee_required',
    ];

    // Standard RM50 annual JBFA affiliate membership fee.
    public const AFFILIATE_FEE = 50.00;

    protected function casts(): array
    {
        return [
            'terms_agreed' => 'boolean',
            'terms_agreed_at' => 'datetime',
            'resubmitted_at' => 'datetime',
            'affiliate_fee_paid' => 'boolean',
            'affiliate_fee_paid_at' => 'datetime',
            'affiliate_fee_reminded_at' => 'datetime',
            'affiliate_fee_required' => 'boolean',
        ];
    }

    /**
     * Does this team owe the RM50 annual affiliate fee? Only league teams that
     * have not been marked exempt (affiliate_fee_required = false) owe it.
     */
    public function owesAffiliateFee(): bool
    {
        return (bool) $this->affiliate_fee_required
            && optional($this->competition)->type === 'league';
    }

    /**
     * The RM50 (or 0 if exempt / not a league team) that applies to this team.
     */
    public function affiliateFeeAmount(): float
    {
        return $this->owesAffiliateFee() ? self::AFFILIATE_FEE : 0.0;
    }

    /**
     * Full registration total for this team = competition base fees + the
     * RM50 annual fee only when the team actually owes it.
     */
    public function registrationTotal(): float
    {
        $comp = $this->competition;
        if (!$comp) {
            return 0.0;
        }

        return (float) $comp->registration_fee
            + (float) $comp->security_deposit
            + (float) $comp->matchday_fee
            + $this->affiliateFeeAmount();
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * The master club this competition entry belongs to.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * The squad shown for this competition entry.
     *
     * Players belong to the CLUB, so the same squad is shared automatically
     * across every competition the club joins. Keyed on club_id (not the team
     * row's id) precisely so a manager registers each player once and it shows
     * up under every competition without re-uploading anything.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'club_id', 'club_id');
    }

    /**
     * The officials shown for this competition entry — shared via the club,
     * exactly like players.
     */
    public function officials(): HasMany
    {
        return $this->hasMany(Official::class, 'club_id', 'club_id');
    }

    /**
     * Team-manager user accounts linked to this team (via the team_user pivot).
     * This is the linkage that controls whether a manager can register players
     * for the team.
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')->withTimestamps();
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


    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = mb_strtoupper(trim($value));
    }

    public function setShortNameAttribute($value): void
    {
        $this->attributes['short_name'] = mb_strtoupper(trim($value));
    }

    public function setManagerNameAttribute($value): void
    {
        $this->attributes['manager_name'] = mb_strtoupper(trim($value));
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(TeamStatusLog::class)->orderBy('created_at', 'desc');
    }
}
