<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchJersey extends Model
{
    protected $table = 'match_jerseys';

    protected $fillable = [
        'match_game_id',
        'team_id',
        'kit_type',
        'shirt_name', 'shirt_hex',
        'shorts_name', 'shorts_hex',
        'socks_name', 'socks_hex',
        'gk_shirt_name', 'gk_shirt_hex',
        'gk_shorts_name', 'gk_shorts_hex',
        'gk_socks_name', 'gk_socks_hex',
        'photo',
        'status',
        'amendment_note',
        'submitted_by',
        'confirmed_by',
        'submitted_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'confirmed_at' => 'datetime',
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

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isAmendmentRequested(): bool
    {
        return $this->status === 'amendment_requested';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'amendment_requested']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Not Submitted',
            'submitted' => 'Submitted',
            'amendment_requested' => 'Amendment Requested',
            'confirmed' => 'Confirmed',
            default => ucfirst($this->status),
        };
    }

    public function statusColour(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'amendment_requested' => 'warning',
            'confirmed' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Convert a hex colour (#rrggbb) to [r, g, b].
     */
    public static function hexToRgb(?string $hex): ?array
    {
        if (!$hex) {
            return null;
        }
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return null;
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Map a hex colour to the nearest common, human-readable colour name.
     * Lets us show a friendly label ("Royal Blue", "White") on the Match
     * Details swatches without asking the Team Manager to type it in.
     */
    public static function colourName(?string $hex): ?string
    {
        if (self::hexToRgb($hex) === null) {
            return null;
        }

        $palette = [
            'White' => '#ffffff', 'Black' => '#000000', 'Grey' => '#808080',
            'Silver' => '#c0c0c0', 'Red' => '#e11d1d', 'Maroon' => '#800000',
            'Claret' => '#7a1f3d', 'Orange' => '#f97316', 'Yellow' => '#facc15',
            'Gold' => '#d4af37', 'Green' => '#22c55e', 'Dark Green' => '#166534',
            'Teal' => '#14b8a6', 'Sky Blue' => '#38bdf8', 'Blue' => '#2563eb',
            'Royal Blue' => '#1e40af', 'Navy' => '#1e2a5a', 'Purple' => '#7c3aed',
            'Pink' => '#ec4899', 'Brown' => '#7c4a1e',
        ];

        $best = null;
        $bestDistance = INF;
        foreach ($palette as $name => $paletteHex) {
            $distance = self::colourDistance($hex, $paletteHex);
            if ($distance !== null && $distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $name;
            }
        }

        return $best;
    }

    /**
     * Euclidean distance between two hex colours (0 = identical, ~441 = max).
     */
    public static function colourDistance(?string $hexA, ?string $hexB): ?float
    {
        $a = self::hexToRgb($hexA);
        $b = self::hexToRgb($hexB);
        if (!$a || !$b) {
            return null;
        }
        return sqrt(
            ($a[0] - $b[0]) ** 2 +
            ($a[1] - $b[1]) ** 2 +
            ($a[2] - $b[2]) ** 2
        );
    }
}
