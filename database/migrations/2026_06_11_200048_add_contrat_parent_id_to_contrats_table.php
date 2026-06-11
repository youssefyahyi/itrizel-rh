<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->foreignId('contrat_parent_id')->nullable()->after('id')
                  ->constrained('contrats')->nullOnDelete();
            $table->string('pdf_path')->nullable()->after('observations');
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropForeign(['contrat_parent_id']);
            $table->dropColumn(['contrat_parent_id', 'pdf_path']);
        });
    }
};
