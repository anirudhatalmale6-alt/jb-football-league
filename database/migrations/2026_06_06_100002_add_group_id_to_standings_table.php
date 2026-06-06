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
        Schema::table('standings', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('team_id')->constrained('groups')->nullOnDelete();

            // Drop old unique constraint and add new one including group_id
            $table->dropUnique(['competition_id', 'team_id']);
            $table->unique(['competition_id', 'team_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standings', function (Blueprint $table) {
            $table->dropUnique(['competition_id', 'team_id', 'group_id']);
            $table->unique(['competition_id', 'team_id']);
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
