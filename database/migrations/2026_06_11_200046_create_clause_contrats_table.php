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
        Schema::create('clause_contrats', function (Blueprint $table) {
            $table->id();
            $table->enum('type_doc', ['CDI', 'CDD', 'avenant']);
            $table->string('titre');
            $table->longText('contenu');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('obligatoire')->default(true);
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->index(['type_doc', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clause_contrats');
    }
};
