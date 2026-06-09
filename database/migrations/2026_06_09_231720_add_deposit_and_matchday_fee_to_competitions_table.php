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
        Schema::table('competitions', function (Blueprint $table) {
            $table->decimal('security_deposit', 10, 2)->default(0)->after('registration_fee');
            $table->decimal('matchday_fee', 10, 2)->default(0)->after('security_deposit');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['security_deposit', 'matchday_fee']);
        });
    }
};
