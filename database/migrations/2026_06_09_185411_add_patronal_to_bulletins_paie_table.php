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
        Schema::table('bulletins_paie', function (Blueprint $table) {
            $table->decimal('prime_anciennete', 10, 2)->default(0)->after('total_primes');
            $table->decimal('cnss_patronal',    10, 2)->default(0)->after('cnss_salarie');
            $table->decimal('amo_patronal',     10, 2)->default(0)->after('amo_salarie');
            $table->decimal('cimr_patronal',    10, 2)->default(0)->after('cimr_salarie');
            $table->decimal('total_patronal',   10, 2)->default(0)->after('total_retenues');
            $table->unsignedSmallInteger('jours_travailles')->default(26)->after('total_patronal');
            $table->unsignedSmallInteger('heures_travailles')->default(191)->after('jours_travailles');
        });
    }

    public function down(): void
    {
        Schema::table('bulletins_paie', function (Blueprint $table) {
            $table->dropColumn([
                'prime_anciennete','cnss_patronal','amo_patronal',
                'cimr_patronal','total_patronal',
                'jours_travailles','heures_travailles',
            ]);
        });
    }
};
