<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standings', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('team_id')->constrained('groups')->nullOnDelete();
        });

        // MySQL needs FK dropped before dropping the unique index it depends on
        DB::statement('ALTER TABLE standings DROP FOREIGN KEY standings_competition_id_foreign');
        DB::statement('ALTER TABLE standings DROP INDEX standings_competition_id_team_id_unique');
        DB::statement('ALTER TABLE standings ADD UNIQUE standings_competition_id_team_id_group_id_unique (competition_id, team_id, group_id)');
        DB::statement('ALTER TABLE standings ADD CONSTRAINT standings_competition_id_foreign FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        Schema::table('standings', function (Blueprint $table) {
            $table->dropUnique(['competition_id', 'team_id', 'group_id']);
            $table->unique(['competition_id', 'team_id']);
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
