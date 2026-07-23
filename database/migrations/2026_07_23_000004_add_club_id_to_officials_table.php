<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Officials belong to the CLUB, mirroring players. Shared across every
 * competition the club joins; `team_id` retained as the origin entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('team_id')
                ->constrained('clubs')->nullOnDelete();
            $table->index(['club_id', 'ic_number']);
        });
    }

    public function down(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->dropIndex(['club_id', 'ic_number']);
            $table->dropForeign(['club_id']);
            $table->dropColumn('club_id');
        });
    }
};
