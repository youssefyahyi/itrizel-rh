<?php

namespace Database\Seeders;

use App\Models\ParametrageRh;
use Illuminate\Database\Seeder;

/**
 * Seeder données société ALF SEBOU — pour tests.
 * Remplit les paramètres "societe.*" avec des données réalistes.
 *
 * Usage : php artisan db:seed --class=ParametrageSocieteSeeder
 */
class ParametrageSocieteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'societe.nom'            => 'ALF SEBOU',
            'societe.forme_juridique'=> 'SARL AU',
            'societe.adresse'     => 'Zone Industrielle Aïn Sebaa, Lot 47',
            'societe.ville'       => 'Kénitra',
            'societe.ice'         => '002345678000091',
            'societe.rc'          => '12345 — Tribunal de Commerce de Kénitra',
            'societe.if'          => '30456789',
            'societe.cnss_patron' => '1234567',
            'societe.telephone'   => '05 37 00 00 01',
            'societe.email'       => 'rh@alfsebou.ma',
        ];

        foreach ($data as $cle => $valeur) {
            ParametrageRh::where('cle', $cle)->update(['valeur' => $valeur]);
        }

        $this->command->info('✅ Données société ALF SEBOU seedées (' . count($data) . ' paramètres).');
    }
}
