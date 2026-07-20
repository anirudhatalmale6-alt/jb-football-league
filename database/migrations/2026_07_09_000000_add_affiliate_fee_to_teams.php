<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('affiliate_fee_paid')->default(false)->after('terms_agreed_by');
            $table->timestamp('affiliate_fee_paid_at')->nullable()->after('affiliate_fee_paid');
            $table->string('affiliate_fee_reference')->nullable()->after('affiliate_fee_paid_at');
            $table->string('affiliate_fee_marked_by')->nullable()->after('affiliate_fee_reference');
            $table->timestamp('affiliate_fee_reminded_at')->nullable()->after('affiliate_fee_marked_by');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_fee_paid',
                'affiliate_fee_paid_at',
                'affiliate_fee_reference',
                'affiliate_fee_marked_by',
                'affiliate_fee_reminded_at',
            ]);
        });
    }
};
