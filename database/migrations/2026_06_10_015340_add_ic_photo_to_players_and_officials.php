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
        Schema::table('players', function (Blueprint $table) {
            $table->string('ic_photo')->nullable()->after('ic_number');
        });
        Schema::table('officials', function (Blueprint $table) {
            $table->string('ic_photo')->nullable()->after('ic_number');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('ic_photo');
        });
        Schema::table('officials', function (Blueprint $table) {
            $table->dropColumn('ic_photo');
        });
    }
};
