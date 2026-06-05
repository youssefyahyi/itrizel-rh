<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->unsignedBigInteger('unite_id')->nullable()->after('manager_id');
            $table->foreign('unite_id')->references('id')->on('unites_organisationnelles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropForeign(['unite_id']);
            $table->dropColumn('unite_id');
        });
    }
};
