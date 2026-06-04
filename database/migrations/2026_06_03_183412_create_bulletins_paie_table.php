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
                Schema::create('bulletins_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_mois');
            $table->unsignedSmallInteger('periode_annee');
            $table->decimal('salaire_base', 10, 2);
            $table->decimal('total_primes', 10, 2)->default(0);
            $table->decimal('total_retenues', 10, 2)->default(0);
            $table->decimal('net_imposable', 10, 2)->default(0);
            $table->decimal('ir_mensuel', 10, 2)->default(0);
            $table->decimal('amo_salarie', 10, 2)->default(0);
            $table->decimal('cnss_salarie', 10, 2)->default(0);
            $table->decimal('cimr_salarie', 10, 2)->default(0);
            $table->decimal('avances_deduites', 10, 2)->default(0);
            $table->decimal('net_a_payer', 10, 2)->default(0);
            $table->enum('statut', ['brouillon', 'valide', 'paye'])->default('brouillon');
            $table->date('date_paiement')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['employe_id', 'periode_mois', 'periode_annee']);
            $table->index(['periode_mois', 'periode_annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins_paie');
    }
};

