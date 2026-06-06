<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_poste', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_id')->constrained('categories_employe')->cascadeOnDelete();
            $table->foreignId('fonction_id')->constrained('fonctions')->cascadeOnDelete();
            $table->foreignId('poste_id')->constrained('postes')->cascadeOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->unique(['categorie_id', 'fonction_id', 'poste_id'], 'uniq_fiche');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_poste');
    }
};
