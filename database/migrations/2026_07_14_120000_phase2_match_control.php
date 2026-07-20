<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Match duration per competition (minutes) so the live clock can follow
        // 90 / 70 / 60 minute formats. Default 90 keeps every existing match the same.
        Schema::table('competitions', function (Blueprint $table) {
            if (!Schema::hasColumn('competitions', 'match_duration')) {
                $table->unsignedSmallInteger('match_duration')->default(90)->after('type');
            }
        });

        // Final-submission audit + early-finish minute on the match.
        Schema::table('match_games', function (Blueprint $table) {
            if (!Schema::hasColumn('match_games', 'final_submitted_at')) {
                $table->timestamp('final_submitted_at')->nullable();
            }
            if (!Schema::hasColumn('match_games', 'final_submitted_by_user_id')) {
                $table->unsignedBigInteger('final_submitted_by_user_id')->nullable();
            }
            if (!Schema::hasColumn('match_games', 'final_minute')) {
                $table->unsignedSmallInteger('final_minute')->nullable();
            }
        });

        // Per-signature remarks (Head Referee / Home Rep / Away Rep / Match Commissioner).
        Schema::table('match_signatures', function (Blueprint $table) {
            if (!Schema::hasColumn('match_signatures', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });

        // Attribution: which user recorded each event (MC work monitoring).
        Schema::table('match_events', function (Blueprint $table) {
            if (!Schema::hasColumn('match_events', 'recorded_by_user_id')) {
                $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            if (Schema::hasColumn('competitions', 'match_duration')) {
                $table->dropColumn('match_duration');
            }
        });
        Schema::table('match_games', function (Blueprint $table) {
            foreach (['final_submitted_at', 'final_submitted_by_user_id', 'final_minute'] as $col) {
                if (Schema::hasColumn('match_games', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('match_signatures', function (Blueprint $table) {
            if (Schema::hasColumn('match_signatures', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
        Schema::table('match_events', function (Blueprint $table) {
            if (Schema::hasColumn('match_events', 'recorded_by_user_id')) {
                $table->dropColumn('recorded_by_user_id');
            }
        });
    }
};
