<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_employe', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->unsignedTinyInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('categories_employe')->insert([
            ['nom' => 'Ouvrier',           'ordre' => 1, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'Employé',           'ordre' => 2, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'Technicien',        'ordre' => 3, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'Agent de maîtrise', 'ordre' => 4, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'Cadre',             'ordre' => 5, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'Cadre supérieur',   'ordre' => 6, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'Dirigeant',         'ordre' => 7, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories_employe');
    }
};
