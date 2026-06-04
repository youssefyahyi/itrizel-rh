<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'gestionnaire', 'lecteur'])->default('gestionnaire')->after('email');
            $table->boolean('actif')->default(true)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('actif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'actif', 'last_login_at']);
        });
    }
};
