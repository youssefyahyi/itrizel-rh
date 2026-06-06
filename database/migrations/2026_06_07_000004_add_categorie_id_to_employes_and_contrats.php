<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->foreignId('categorie_id')
                  ->nullable()
                  ->after('categorie')
                  ->constrained('categories_employe')
                  ->nullOnDelete();
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->foreignId('categorie_id')
                  ->nullable()
                  ->after('categorie')
                  ->constrained('categories_employe')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');
        });
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');
        });
    }
};
