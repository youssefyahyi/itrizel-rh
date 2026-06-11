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
        Schema::table('employes', function (Blueprint $table) {
            $table->decimal('quotite_travail', 5, 2)->default(100.00)->after('situation_familiale');
            $table->decimal('heures_semaine',  5, 2)->default(44.00)->after('quotite_travail');
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn(['quotite_travail', 'heures_semaine']);
        });
    }
};
