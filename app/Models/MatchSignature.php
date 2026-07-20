<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchSignature extends Model
{
    protected $fillable = [
        'match_game_id',
        'role',
        'name',
        'signature_data',
        'confirmed',
        'signed_at',
        'signed_by_user_id',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'confirmed' => 'boolean',
            'signed_at' => 'datetime',
        ];
    }

    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }

    public static function roleLabel(string $role): string
    {
        return match($role) {
            'head_referee' => 'Head Referee',
            'home_team_rep' => 'Home Team Representative',
            'away_team_rep' => 'Away Team Representative',
            'match_commissioner' => 'Match Commissioner / League Admin',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    public static function roleLabelMs(string $role): string
    {
        return match($role) {
            'head_referee' => 'Pengadil Utama',
            'home_team_rep' => 'Wakil Pasukan Tuan Rumah',
            'away_team_rep' => 'Wakil Pasukan Pelawat',
            'match_commissioner' => 'Pesuruhjaya Perlawanan',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }
}
