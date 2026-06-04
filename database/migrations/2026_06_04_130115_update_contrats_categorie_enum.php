<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE contrats MODIFY categorie ENUM('commercial','chauffeur','magasinier','logisticien','administratif','cadre') NOT NULL");
        DB::statement("ALTER TABLE contrats MODIFY type ENUM('CDD','CDI','interim','vacataire') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE contrats MODIFY categorie ENUM('professeur','charge_cours','administratif','technique') NOT NULL");
        DB::statement("ALTER TABLE contrats MODIFY type ENUM('CDD','CDI','vacataire') NOT NULL");
    }
};