<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codification_parametres', function (Blueprint $table) {
            $table->id();
            $table->string('entite')->unique();           // matricule, contrat, avenant
            $table->string('libelle');                    // label affichage
            $table->string('prefixe')->default('');
            $table->string('separateur')->default('-');
            $table->boolean('inclure_annee')->default(true);
            $table->enum('format_annee', ['YYYY', 'YY'])->default('YYYY');
            $table->tinyInteger('nb_chiffres')->default(4);
            $table->boolean('reset_annuel')->default(true);
            $table->integer('compteur_courant')->default(0);
            $table->smallInteger('annee_courante')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codification_parametres');
    }
};
