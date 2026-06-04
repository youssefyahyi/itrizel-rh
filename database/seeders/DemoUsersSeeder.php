<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // â”€â”€ Ã‰quipes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $teams = [
            ['name' => 'Direction GÃ©nÃ©rale',  'description' => 'Ã‰quipe de direction et pilotage stratÃ©gique', 'couleur' => '#7C3AED', 'actif' => true],
            ['name' => 'Service MarchÃ©s',     'description' => 'Gestion des appels d\'offres et marchÃ©s publics',  'couleur' => '#2563EB', 'actif' => true],
            ['name' => 'Service Technique',   'description' => 'Suivi technique des projets et travaux',          'couleur' => '#059669', 'actif' => true],
            ['name' => 'Service Financier',   'description' => 'ContrÃ´le financier et dÃ©comptes',                'couleur' => '#D97706', 'actif' => true],
        ];

        $teamIds = [];
        foreach ($teams as $t) {
            $id = DB::table('teams')->insertGetId(array_merge($t, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
            $teamIds[] = $id;
        }

        // â”€â”€ Utilisateurs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $users = [
            [
                'name'               => 'Administrateur',
                'email'              => 'admin@itrizel-rh.ma',
                'password'           => Hash::make('Admin1234!'),
                'role'               => 'admin',
                'actif'              => true,
                'telephone'          => '+212 6 00 00 00 01',
                'team_id'            => $teamIds[0],
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Youssef El Mansouri',
                'email'              => 'y.elmansouri@gcm.ma',
                'password'           => Hash::make('Demo2026!'),
                'role'               => 'gestionnaire',
                'actif'              => true,
                'telephone'          => '+212 6 61 23 45 67',
                'team_id'            => $teamIds[1],
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Fatima Benali',
                'email'              => 'f.benali@gcm.ma',
                'password'           => Hash::make('Demo2026!'),
                'role'               => 'gestionnaire',
                'actif'              => true,
                'telephone'          => '+212 6 62 34 56 78',
                'team_id'            => $teamIds[1],
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Hassan El Mourabit',
                'email'              => 'h.elmourabit@gcm.ma',
                'password'           => Hash::make('Demo2026!'),
                'role'               => 'gestionnaire',
                'actif'              => true,
                'telephone'          => '+212 6 63 45 67 89',
                'team_id'            => $teamIds[2],
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Khadija Zouiten',
                'email'              => 'k.zouiten@gcm.ma',
                'password'           => Hash::make('Demo2026!'),
                'role'               => 'lecteur',
                'actif'              => true,
                'telephone'          => '+212 6 64 56 78 90',
                'team_id'            => $teamIds[3],
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Omar Boulahia',
                'email'              => 'o.boulahia@gcm.ma',
                'password'           => Hash::make('Demo2026!'),
                'role'               => 'lecteur',
                'actif'              => false,
                'telephone'          => null,
                'team_id'            => null,
                'email_verified_at'  => now(),
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }

        $this->command->info('âœ… Jeu de test Administration crÃ©Ã© :');
        $this->command->info('   - ' . count($teams) . ' Ã©quipes');
        $this->command->info('   - ' . count($users) . ' utilisateurs (admin / gestionnaire / lecteur / inactif)');
    }
}

