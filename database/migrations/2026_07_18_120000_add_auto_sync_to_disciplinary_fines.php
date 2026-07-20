<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New fine types generated automatically from match card events.
        DB::statement("ALTER TABLE disciplinary_fines MODIFY COLUMN fine_type ENUM('red_card','red_direct','red_second_yellow','yellow_accumulation','misconduct','late_arrival','walkover','other') NOT NULL");

        // Auto-generated fines have no human issuer.
        DB::statement("ALTER TABLE disciplinary_fines MODIFY issued_by BIGINT UNSIGNED NULL");

        Schema::table('disciplinary_fines', function (Blueprint $table) {
            // 'manual' = issued by an admin, 'auto' = generated from match events.
            $table->string('source', 20)->default('manual')->after('issued_by');
            // Idempotency key so re-syncing never duplicates a fine.
            $table->string('auto_key')->nullable()->unique()->after('source');
            // The card event this fine was generated from (per-match reds).
            $table->unsignedBigInteger('source_event_id')->nullable()->after('auto_key');
            // Card metadata surfaced in the fine record.
            $table->string('card_type', 20)->nullable()->after('source_event_id');
            $table->integer('card_minute')->nullable()->after('card_type');
        });
    }

    public function down(): void
    {
        Schema::table('disciplinary_fines', function (Blueprint $table) {
            $table->dropColumn(['source', 'auto_key', 'source_event_id', 'card_type', 'card_minute']);
        });

        DB::statement("ALTER TABLE disciplinary_fines MODIFY COLUMN fine_type ENUM('red_card','yellow_accumulation','misconduct','late_arrival','walkover','other') NOT NULL");
    }
};
