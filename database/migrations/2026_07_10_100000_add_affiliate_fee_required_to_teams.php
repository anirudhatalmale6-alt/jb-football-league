<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Whether this team must pay the RM50 annual JBFA membership fee.
            // Default true keeps the current behaviour (every league team owes it);
            // admins can mark specific teams as exempt so their total drops by RM50.
            $table->boolean('affiliate_fee_required')->default(true)->after('affiliate_fee_reminded_at');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('affiliate_fee_required');
        });
    }
};
