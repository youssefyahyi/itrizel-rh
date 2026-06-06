<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->foreignId('poste_id')
                  ->nullable()
                  ->after('poste')
                  ->constrained('postes')
                  ->nullOnDelete();
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->foreignId('poste_id')
                  ->nullable()
                  ->after('poste')
                  ->constrained('postes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropForeign(['poste_id']);
            $table->dropColumn('poste_id');
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->dropForeign(['poste_id']);
            $table->dropColumn('poste_id');
        });
    }
};
