<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projection_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->tinyInteger('horizon')->default(12); // 6, 12 ou 24
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projection_scenario_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained('projection_scenarios')->cascadeOnDelete();
            $table->enum('type', ['revalorisation', 'embauche', 'depart']);
            $table->date('mois_effet'); // premier jour du mois concerné
            // Revalorisation
            $table->decimal('pourcentage', 5, 2)->nullable();
            $table->foreignId('categorie_id')->nullable()->constrained('categories_employe')->nullOnDelete();
            // Embauche
            $table->decimal('salaire_estime', 10, 2)->nullable();
            // Départ
            $table->foreignId('employe_id')->nullable()->constrained('employes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projection_scenario_lignes');
        Schema::dropIfExists('projection_scenarios');
    }
};
