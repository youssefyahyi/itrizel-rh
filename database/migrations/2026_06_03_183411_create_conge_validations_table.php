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
                Schema::create('conge_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conge_id')->constrained('conges')->cascadeOnDelete();
            $table->unsignedTinyInteger('etape');
            $table->foreignId('valideur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegataire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['approuve', 'rejete', 'renvoi']);
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->index(['conge_id', 'etape']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conge_validations');
    }
};

