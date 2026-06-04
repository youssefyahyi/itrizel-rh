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
                Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->foreignId('evaluateur_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['semestriel', 'annuel', 'periode_essai']);
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->decimal('note_globale', 4, 2)->nullable();
            $table->enum('statut', ['brouillon', 'finalise'])->default('brouillon');
            $table->text('observations')->nullable();
            $table->enum('decision', ['renouvellement', 'non_renouvellement', 'amelioration', 'en_attente'])->default('en_attente');
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
        Schema::dropIfExists('evaluations');
    }
};

