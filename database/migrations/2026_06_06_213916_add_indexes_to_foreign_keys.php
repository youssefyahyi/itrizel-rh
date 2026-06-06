<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index explicites sur les clés étrangères pour optimiser les jointures.
     * MySQL crée ces index automatiquement via les FK, mais les déclarer
     * explicitement est une bonne pratique et facilite l'inspection du schéma.
     */
    public function up(): void
    {
        // employes : manager_id, unite_id
        Schema::table('employes', function (Blueprint $table) {
            if (!$this->indexExists('employes', 'employes_manager_id_index')) {
                $table->index('manager_id', 'employes_manager_id_index');
            }
            if (!$this->indexExists('employes', 'employes_unite_id_index')) {
                $table->index('unite_id', 'employes_unite_id_index');
            }
        });

        // users : employe_id
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_employe_id_index')) {
                $table->index('employe_id', 'users_employe_id_index');
            }
        });

        // unites_organisationnelles : parent_id, responsable_id
        Schema::table('unites_organisationnelles', function (Blueprint $table) {
            if (!$this->indexExists('unites_organisationnelles', 'unites_parent_id_index')) {
                $table->index('parent_id', 'unites_parent_id_index');
            }
            if (!$this->indexExists('unites_organisationnelles', 'unites_responsable_id_index')) {
                $table->index('responsable_id', 'unites_responsable_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropIndex('employes_manager_id_index');
            $table->dropIndex('employes_unite_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_employe_id_index');
        });

        Schema::table('unites_organisationnelles', function (Blueprint $table) {
            $table->dropIndex('unites_parent_id_index');
            $table->dropIndex('unites_responsable_id_index');
        });
    }

    /**
     * Vérifie si un index existe déjà (MySQL crée automatiquement des index sur les FK).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        return count(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        )) > 0;
    }
};
