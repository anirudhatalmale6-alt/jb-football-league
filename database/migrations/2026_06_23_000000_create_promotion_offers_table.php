<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('from_competition_id');
            $table->unsignedBigInteger('to_competition_id');
            $table->unsignedBigInteger('offered_by');
            $table->enum('status', ['pending', 'accepted', 'expired', 'declined'])->default('pending');
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('coaching_license')->nullable();
            $table->boolean('fee_agreed')->default(false);
            $table->timestamp('offered_at');
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('from_competition_id')->references('id')->on('competitions');
            $table->foreign('to_competition_id')->references('id')->on('competitions');
            $table->foreign('offered_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_offers');
    }
};
