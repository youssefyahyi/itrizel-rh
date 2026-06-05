<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Lien user ↔ employé (pour le rôle salarié)
            $table->unsignedBigInteger('employe_id')->nullable()->after('team_id');
            $table->foreign('employe_id')->references('id')->on('employes')->nullOnDelete();
        });

        // Étendre l'enum role pour ajouter 'salarie'
        // MySQL : on doit modifier la colonne
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','gestionnaire','lecteur','salarie') DEFAULT 'gestionnaire'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employe_id']);
            $table->dropColumn('employe_id');
        });
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','gestionnaire','lecteur') DEFAULT 'gestionnaire'");
    }
};
