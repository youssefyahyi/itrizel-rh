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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('cin')->unique();
            $table->date('date_naissance');
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->default('Marocaine');
            $table->enum('sexe', ['M', 'F']);
            $table->string('email')->unique()->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('photo')->nullable();
            $table->string('diplome')->nullable();
            $table->string('specialite')->nullable();
            $table->enum('categorie', ['professeur', 'charge_cours', 'administratif', 'technique']);
            $table->string('poste');
            $table->date('date_embauche');
            $table->string('rib')->nullable();
            $table->string('banque')->nullable();
            $table->string('numero_cnss')->nullable();
            $table->string('numero_amo')->nullable();
            $table->unsignedTinyInteger('nombre_enfants')->default(0);
            $table->enum('situation_familiale', ['celibataire', 'marie', 'divorce', 'veuf'])->default('celibataire');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('categorie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
