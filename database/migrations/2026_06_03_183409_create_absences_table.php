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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->date('date');
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            $table->decimal('heures_prevues', 4, 2)->default(8);
            $table->decimal('heures_realisees', 4, 2)->nullable();
            $table->enum('statut', ['present', 'absent', 'conge', 'ferie', 'mission'])->default('present');
            $table->string('motif_absence')->nullable();
            $table->string('justificatif')->nullable();
            $table->text('remarque')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['employe_id', 'date']);
            $table->index(['date', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
