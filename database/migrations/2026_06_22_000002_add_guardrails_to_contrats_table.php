<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // CHECK : date_fin >= date_debut quand date_fin est renseignée
        DB::statement('ALTER TABLE contrats ADD CONSTRAINT chk_contrats_dates
            CHECK (date_fin IS NULL OR date_fin >= date_debut)');

        // CHECK : salaire_base strictement positif
        DB::statement('ALTER TABLE contrats ADD CONSTRAINT chk_contrats_salaire
            CHECK (salaire_base > 0)');

        // CHECK : CDD / CDDI / intérim doivent avoir une date_fin
        DB::statement("ALTER TABLE contrats ADD CONSTRAINT chk_contrats_cdd_date_fin
            CHECK (type NOT IN ('CDD','CDDI','interim') OR date_fin IS NOT NULL)");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE contrats DROP CONSTRAINT IF EXISTS chk_contrats_dates');
        DB::statement('ALTER TABLE contrats DROP CONSTRAINT IF EXISTS chk_contrats_salaire');
        DB::statement('ALTER TABLE contrats DROP CONSTRAINT IF EXISTS chk_contrats_cdd_date_fin');
    }
};
