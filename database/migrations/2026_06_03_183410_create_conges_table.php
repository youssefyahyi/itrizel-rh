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
                Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->enum('type_conge', ['annuel', 'maladie', 'maternite', 'paternite', 'sans_solde', 'exceptionnel', 'recuperation']);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->unsignedSmallInteger('nb_jours');
            $table->text('motif')->nullable();
            $table->enum('statut', ['soumis', 'en_validation', 'approuve', 'rejete', 'annule'])->default('soumis');
            $table->unsignedTinyInteger('etape_actuelle')->default(1);
            $table->string('document_justificatif')->nullable();
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
        Schema::dropIfExists('conges');
    }
};

