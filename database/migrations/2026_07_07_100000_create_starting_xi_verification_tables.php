<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starting_xi_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_game_id')->constrained('match_games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('photo');
            // uploaded -> under_review -> verified | possible_mismatch
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
            // confirmed = matches line-up, flagged = possible identity mismatch under investigation
            $table->enum('result', ['confirmed', 'flagged'])->default('confirmed');
            $table->text('remarks')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_result')->nullable();
            $table->timestamps();

            $table->unique(['match_game_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_verification_checks');
        Schema::dropIfExists('starting_xi_photos');
    }
};
