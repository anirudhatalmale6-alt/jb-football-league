<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_offers', function (Blueprint $table) {
            // When true, the team can only ACCEPT this offer - the decline
            // option is hidden on the response page and blocked server-side.
            $table->boolean('accept_only')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_offers', function (Blueprint $table) {
            $table->dropColumn('accept_only');
        });
    }
};
