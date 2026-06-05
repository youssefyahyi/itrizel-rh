<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unites_organisationnelles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->enum('type', ['direction', 'departement', 'service', 'equipe', 'autre'])->default('service');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('unites_organisationnelles')->nullOnDelete();
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->foreign('responsable_id')->references('id')->on('employes')->nullOnDelete();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unites_organisationnelles');
    }
};
