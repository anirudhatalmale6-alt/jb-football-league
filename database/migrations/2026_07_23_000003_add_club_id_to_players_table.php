<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Players now belong to the CLUB, not to a per-competition team entry.
 *
 * `team_id` is kept as the player's origin/"home" entry for backward
 * compatibility and history, but `club_id` is the ownership key the app reads:
 * every competition entry of the club shares the same squad through it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('team_id')
                ->constrained('clubs')->nullOnDelete();
            $table->index(['club_id', 'ic_number']);
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            // Drop the FK first — it relies on an index over club_id — then the
            // composite index, then the column itself.
            $table->dropForeign(['club_id']);
            $table->dropIndex(['club_id', 'ic_number']);
            $table->dropColumn('club_id');
        });
    }
};
