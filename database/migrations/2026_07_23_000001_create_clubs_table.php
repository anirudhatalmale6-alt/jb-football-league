<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The master "club" entity.
 *
 * Until now a club existed only implicitly: every competition a club joined
 * created its own `teams` row (with its own duplicated players/officials), and
 * "same club" was inferred purely from a matching name. This table gives the
 * club a real, durable identity that owns ONE squad and ONE officials list,
 * which every competition entry (a `teams` row) then references.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
