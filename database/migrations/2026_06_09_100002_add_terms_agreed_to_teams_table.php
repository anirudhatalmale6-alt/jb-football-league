<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('terms_agreed')->default(false)->after('status');
            $table->timestamp('terms_agreed_at')->nullable()->after('terms_agreed');
            $table->string('terms_agreed_by')->nullable()->after('terms_agreed_at');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['terms_agreed', 'terms_agreed_at', 'terms_agreed_by']);
        });
    }
};
