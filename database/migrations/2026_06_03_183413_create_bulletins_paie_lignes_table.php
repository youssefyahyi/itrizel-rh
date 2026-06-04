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
                Schema::create('bulletins_paie_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_id')->constrained('bulletins_paie')->cascadeOnDelete();
            $table->enum('type', ['prime', 'retenue', 'cotisation']);
            $table->string('libelle');
            $table->decimal('taux', 6, 4)->nullable();
            $table->decimal('base', 10, 2)->nullable();
            $table->decimal('montant', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins_paie_lignes');
    }
};

