<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 50);
            $table->string('nom', 100);
            $table->json('filtres');
            $table->enum('visibilite', ['prive', 'equipe', 'public'])->default('prive');
            $table->timestamps();

            $table->index(['module', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_views');
    }
};
