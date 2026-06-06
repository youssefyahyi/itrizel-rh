<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn(['poste', 'categorie']);
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->dropColumn(['poste', 'categorie']);
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->string('categorie', 50)->nullable()->after('specialite');
            $table->string('poste', 100)->nullable()->after('categorie');
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->string('categorie', 50)->nullable()->after('type');
            $table->string('poste', 100)->nullable()->after('categorie');
        });
    }
};
