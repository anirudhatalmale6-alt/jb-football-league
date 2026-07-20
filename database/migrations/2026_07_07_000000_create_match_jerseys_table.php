<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_jerseys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_game_id')->constrained('match_games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->enum('kit_type', ['primary', 'alternative'])->default('primary');

            // Outfield players
            $table->string('shirt_name')->nullable();
            $table->string('shirt_hex', 7)->nullable();
            $table->string('shorts_name')->nullable();
            $table->string('shorts_hex', 7)->nullable();
            $table->string('socks_name')->nullable();
            $table->string('socks_hex', 7)->nullable();

            // Goalkeeper
            $table->string('gk_shirt_name')->nullable();
            $table->string('gk_shirt_hex', 7)->nullable();
            $table->string('gk_shorts_name')->nullable();
            $table->string('gk_shorts_hex', 7)->nullable();
            $table->string('gk_socks_name')->nullable();
            $table->string('gk_socks_hex', 7)->nullable();

            $table->string('photo')->nullable();

            // draft -> submitted -> (amendment_requested) -> confirmed
            $table->enum('status', ['draft', 'submitted', 'amendment_requested', 'confirmed'])->default('draft');
            $table->text('amendment_note')->nullable();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['match_game_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_jerseys');
    }
};
