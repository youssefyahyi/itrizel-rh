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
                Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->enum('type', ['CDD', 'CDI', 'vacataire']);
            $table->string('poste');
            $table->enum('categorie', ['professeur', 'charge_cours', 'administratif', 'technique']);
            $table->decimal('salaire_base', 10, 2);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->unsignedSmallInteger('duree_mois')->nullable();
            $table->boolean('renouvellement_auto')->default(false);
            $table->enum('statut', ['en_cours', 'expire', 'renouvele', 'resilie', 'cloture'])->default('en_cours');
            $table->string('motif_resiliation')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['employe_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};

