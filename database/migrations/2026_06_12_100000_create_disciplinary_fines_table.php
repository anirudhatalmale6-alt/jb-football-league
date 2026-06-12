<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('match_game_id')->nullable()->constrained('match_games')->nullOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->enum('fine_type', ['red_card', 'yellow_accumulation', 'misconduct', 'late_arrival', 'walkover', 'other']);
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_fines');
    }
};
