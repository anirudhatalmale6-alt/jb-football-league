<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link every competition entry (`teams` row) to its master club.
 *
 * A club that plays in the Super League, the FA Cup and the Sumbangsih Cup has
 * three `teams` rows — all three now point at the same `club_id`. Nullable so
 * the column can be added ahead of the back-fill; the consolidation command
 * fills it in for every existing row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('id')
                ->constrained('clubs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropColumn('club_id');
        });
    }
};
