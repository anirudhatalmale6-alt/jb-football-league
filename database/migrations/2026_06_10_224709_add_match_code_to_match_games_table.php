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
        Schema::table('match_games', function (Blueprint $table) {
            $table->string('match_code')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            $table->dropColumn('match_code');
        });
    }
};
