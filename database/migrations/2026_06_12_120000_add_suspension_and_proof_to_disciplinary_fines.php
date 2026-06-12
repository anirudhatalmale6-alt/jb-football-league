<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disciplinary_fines', function (Blueprint $table) {
            $table->string('proof_file')->nullable()->after('payment_url');
            $table->boolean('is_suspended')->default(false)->after('notes');
            $table->enum('suspension_type', ['until_paid', 'match_ban'])->nullable()->after('is_suspended');
            $table->integer('suspension_matches')->nullable()->after('suspension_type');
            $table->integer('matches_served')->default(0)->after('suspension_matches');
            $table->timestamp('suspension_lifted_at')->nullable()->after('matches_served');
            $table->string('suspension_lifted_by')->nullable()->after('suspension_lifted_at');
        });
    }

    public function down(): void
    {
        Schema::table('disciplinary_fines', function (Blueprint $table) {
            $table->dropColumn([
                'proof_file',
                'is_suspended',
                'suspension_type',
                'suspension_matches',
                'matches_served',
                'suspension_lifted_at',
                'suspension_lifted_by',
            ]);
        });
    }
};
