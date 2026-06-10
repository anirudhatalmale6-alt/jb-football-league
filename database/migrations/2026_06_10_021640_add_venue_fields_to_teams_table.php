<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->after('applicant_position');
            $table->string('venue_location')->nullable()->after('venue_name');
            $table->string('venue_coordinator_name')->nullable()->after('venue_location');
            $table->string('venue_coordinator_phone')->nullable()->after('venue_coordinator_name');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['venue_name', 'venue_location', 'venue_coordinator_name', 'venue_coordinator_phone']);
        });
    }
};
