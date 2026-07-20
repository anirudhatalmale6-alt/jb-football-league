<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add the two new match-operations roles to the users.role enum.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','league_admin','team_manager','public','head_match_commissioner','match_commissioner') NOT NULL DEFAULT 'public'");

        // Which Match Commissioner is assigned to run this match.
        Schema::table('match_games', function (Blueprint $table) {
            if (!Schema::hasColumn('match_games', 'assigned_mc_user_id')) {
                $table->unsignedBigInteger('assigned_mc_user_id')->nullable()->index();
            }
        });

        // Full audit trail of MC assignments / reassignments.
        if (!Schema::hasTable('mc_assignment_logs')) {
            Schema::create('mc_assignment_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('match_game_id')->index();
                $table->unsignedBigInteger('previous_mc_user_id')->nullable();
                $table->unsignedBigInteger('new_mc_user_id')->nullable();
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            if (Schema::hasColumn('match_games', 'assigned_mc_user_id')) {
                $table->dropColumn('assigned_mc_user_id');
            }
        });
        Schema::dropIfExists('mc_assignment_logs');
        // Revert enum (only safe if no user holds a new role).
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','league_admin','team_manager','public') NOT NULL DEFAULT 'public'");
    }
};
