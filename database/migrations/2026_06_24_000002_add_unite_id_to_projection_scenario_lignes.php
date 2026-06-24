<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projection_scenario_lignes', function (Blueprint $table) {
            $table->foreignId('unite_id')
                ->nullable()
                ->after('employe_id')
                ->constrained('unites_organisationnelles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projection_scenario_lignes', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\UniteOrganisationnelle::class);
            $table->dropColumn('unite_id');
        });
    }
};
