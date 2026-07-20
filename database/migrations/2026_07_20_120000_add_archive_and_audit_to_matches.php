<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Archive support on the match itself: an archived match is hidden from
        // normal listings but keeps every related record intact.
        Schema::table('match_games', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('assigned_mc_user_id');
            $table->foreignId('archived_by_user_id')->nullable()->after('archived_at')
                ->constrained('users')->nullOnDelete();
            $table->string('archive_reason')->nullable()->after('archived_by_user_id');
        });

        // Immutable audit trail of every archive / delete / restore action.
        // Match details are snapshotted as text so the record survives even
        // after the match row itself is permanently deleted.
        Schema::create('match_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_game_id')->nullable();
            $table->string('action', 20); // archived, restored, deleted
            $table->string('match_code')->nullable();
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->string('competition')->nullable();
            $table->dateTime('match_date')->nullable();
            $table->string('status_at_action', 30)->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_name')->nullable();
            $table->timestamps();

            $table->index('match_game_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_audit_logs');
        Schema::table('match_games', function (Blueprint $table) {
            $table->dropConstrainedForeignId('archived_by_user_id');
            $table->dropColumn(['archived_at', 'archive_reason']);
        });
    }
};
