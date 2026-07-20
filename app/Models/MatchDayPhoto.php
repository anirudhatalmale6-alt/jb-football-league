<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single Match Day photographic record. Exactly one row per (match, category).
 * This is NOT a verification record - it only keeps official JBFA photos.
 */
class MatchDayPhoto extends Model
{
    protected $table = 'match_day_photos';

    public const CATEGORY_HOME_XI = 'home_xi';
    public const CATEGORY_AWAY_XI = 'away_xi';
    public const CATEGORY_REFEREE_CAPTAINS = 'referee_captains';

    /** The three compulsory categories, in display order. */
    public const CATEGORIES = [
        self::CATEGORY_HOME_XI,
        self::CATEGORY_AWAY_XI,
        self::CATEGORY_REFEREE_CAPTAINS,
    ];

    protected $fillable = [
        'match_game_id',
        'category',
        'team_id',
        'photo',
        'uploaded_by',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** English label for a category key. */
    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_HOME_XI => 'Home Team Starting XI',
            self::CATEGORY_AWAY_XI => 'Away Team Starting XI',
            self::CATEGORY_REFEREE_CAPTAINS => 'Referee & Both Captains',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    /** Translation key used for the category label. */
    public static function categoryLangKey(string $category): string
    {
        return match ($category) {
            self::CATEGORY_HOME_XI => 'mdp_home_xi',
            self::CATEGORY_AWAY_XI => 'mdp_away_xi',
            self::CATEGORY_REFEREE_CAPTAINS => 'mdp_referee_captains',
            default => 'mdp_home_xi',
        };
    }
}
