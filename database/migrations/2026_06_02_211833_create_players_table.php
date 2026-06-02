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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->integer('jersey_number');
            $table->enum('position', ['goalkeeper', 'defender', 'midfielder', 'forward']);
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('ic_number')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'injured', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
