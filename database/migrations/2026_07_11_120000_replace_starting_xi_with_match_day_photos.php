<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces the old Starting XI *Verification* feature (starting_xi_photos +
 * player_verification_checks) with a simple, private "Match Day Photos" record:
 * exactly 3 categorised photos per match, no verification workflow at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_day_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_game_id')->constrained('match_games')->cascadeOnDelete();
            // home_xi | away_xi | referee_captains
            $table->string('category');
            // team_id is set for home_xi / away_xi, null for referee_captains
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('photo'); // path relative to the private "local" disk
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->unique(['match_game_id', 'category']);
        });

        // Best-effort migration of any existing Starting XI photos into the new
        // structure so nothing already uploaded is lost.
        if (Schema::hasTable('starting_xi_photos')) {
            $rows = DB::table('starting_xi_photos')->get();
            foreach ($rows as $row) {
                $match = DB::table('match_games')->where('id', $row->match_game_id)->first();
                if (!$match) {
                    continue;
                }

                if ((int) $row->team_id === (int) $match->home_team_id) {
                    $category = 'home_xi';
                } elseif ((int) $row->team_id === (int) $match->away_team_id) {
                    $category = 'away_xi';
                } else {
                    continue;
                }

                // Move the physical file from the public disk into the private disk.
                $newPath = 'match-day-photos/' . $row->match_game_id . '/' . basename($row->photo);
                try {
                    if (Storage::disk('public')->exists($row->photo)) {
                        $contents = Storage::disk('public')->get($row->photo);
                        Storage::disk('local')->put($newPath, $contents);
                        Storage::disk('public')->delete($row->photo);
                    }
                } catch (\Throwable $e) {
                    // If the file can't be moved we still keep the DB record pointing
                    // at the intended location; a re-upload will fix it.
                }

                DB::table('match_day_photos')->updateOrInsert(
                    ['match_game_id' => $row->match_game_id, 'category' => $category],
                    [
                        'team_id' => $row->team_id,
                        'photo' => $newPath,
                        'uploaded_by' => $row->uploaded_by ?? null,
                        'uploaded_at' => $row->uploaded_at ?? $row->created_at ?? now(),
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        Schema::dropIfExists('player_verification_checks');
        Schema::dropIfExists('starting_xi_photos');
    }

    public function down(): void
    {
        // Recreate the old tables (empty) so the migration is reversible.
        Schema::create('starting_xi_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_game_id')->constrained('match_games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('photo');
            $table->enum('status', ['uploaded', 'under_review', 'verified', 'possible_mismatch'])->default('uploaded');
            $table->text('remarks')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['match_game_id', 'team_id']);
        });

        Schema::create('player_verification_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_game_id')->constrained('match_games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('result', ['confirmed', 'flagged'])->default('confirmed');
            $table->text('remarks')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_result')->nullable();
            $table->timestamps();
            $table->unique(['match_game_id', 'player_id']);
        });

        Schema::dropIfExists('match_day_photos');
    }
};
