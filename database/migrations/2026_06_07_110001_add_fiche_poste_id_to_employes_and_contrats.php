<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->foreignId('fiche_poste_id')
                  ->nullable()
                  ->after('poste')
                  ->constrained('fiches_poste')
                  ->nullOnDelete();
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->foreignId('fiche_poste_id')
                  ->nullable()
                  ->after('poste')
                  ->constrained('fiches_poste')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropForeign(['fiche_poste_id']);
            $table->dropColumn('fiche_poste_id');
        });
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropForeign(['fiche_poste_id']);
            $table->dropColumn('fiche_poste_id');
        });
    }
};
