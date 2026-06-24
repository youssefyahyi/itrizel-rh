<?php

namespace App\Services;

use App\Models\{Employe, Contrat, UniteOrganisationnelle};

class ImportService
{
    public static array $colonnes = [
        'employes' => [
            ['col' => 'nom',                 'libelle' => 'Nom *',                        'exemple' => 'BENALI',           'obligatoire' => true],
            ['col' => 'prenom',              'libelle' => 'Prénom *',                     'exemple' => 'Zakaria',          'obligatoire' => true],
            ['col' => 'cin',                 'libelle' => 'CIN *',                        'exemple' => 'BE123456',         'obligatoire' => true],
            ['col' => 'sexe',                'libelle' => 'Sexe (H/F)',                   'exemple' => 'H',                'obligatoire' => false],
            ['col' => 'date_naissance',      'libelle' => 'Date naissance (JJ/MM/AAAA)',  'exemple' => '15/03/1988',       'obligatoire' => false],
            ['col' => 'date_embauche',       'libelle' => 'Date embauche * (JJ/MM/AAAA)', 'exemple' => '01/01/2020',      'obligatoire' => true],
            ['col' => 'email',               'libelle' => 'Email',                        'exemple' => 'z.benali@ex.ma',   'obligatoire' => false],
            ['col' => 'telephone',           'libelle' => 'Téléphone',                    'exemple' => '0661234567',       'obligatoire' => false],
            ['col' => 'situation_familiale', 'libelle' => 'Situation familiale',          'exemple' => 'marie',            'obligatoire' => false],
            ['col' => 'nombre_enfants',      'libelle' => 'Nb enfants',                   'exemple' => '2',                'obligatoire' => false],
            ['col' => 'unite',               'libelle' => 'Unité (nom exact)',             'exemple' => 'Direction',        'obligatoire' => false],
        ],
        'contrats' => [
            ['col' => 'matricule',    'libelle' => 'Matricule * (existant)',              'exemple' => 'EMP-2026-0001',    'obligatoire' => true],
            ['col' => 'type',         'libelle' => 'Type * (CDI/CDD/interim/vacataire)', 'exemple' => 'CDI',              'obligatoire' => true],
            ['col' => 'date_debut',   'libelle' => 'Date début * (JJ/MM/AAAA)',          'exemple' => '01/01/2020',       'obligatoire' => true],
            ['col' => 'date_fin',     'libelle' => 'Date fin (JJ/MM/AAAA)',              'exemple' => '31/12/2026',       'obligatoire' => false],
            ['col' => 'salaire_base', 'libelle' => 'Salaire base * (DH)',                'exemple' => '5000',             'obligatoire' => true],
        ],
    ];

    public function valider(array $data, string $entite): array
    {
        $erreurs = [];

        if ($entite === 'employes') {
            if (empty($data['nom']))           $erreurs[] = 'Nom obligatoire';
            if (empty($data['prenom']))        $erreurs[] = 'Prénom obligatoire';
            if (empty($data['cin']))           $erreurs[] = 'CIN obligatoire';
            if (empty($data['date_embauche'])) $erreurs[] = 'Date embauche obligatoire';
            elseif (!$this->dateValide($data['date_embauche'])) $erreurs[] = 'Date embauche invalide (JJ/MM/AAAA)';
            if (!empty($data['date_naissance']) && !$this->dateValide($data['date_naissance'])) $erreurs[] = 'Date naissance invalide';
            if (!empty($data['sexe']) && !in_array(strtoupper($data['sexe']), ['H', 'F'])) $erreurs[] = 'Sexe doit être H ou F';
            if (!empty($data['cin']) && Employe::where('cin', $data['cin'])->exists()) $erreurs[] = "CIN {$data['cin']} déjà enregistré";
            if (!empty($data['unite']) && !UniteOrganisationnelle::where('nom', $data['unite'])->exists()) $erreurs[] = "Unité « {$data['unite']} » introuvable";
        }

        if ($entite === 'contrats') {
            if (empty($data['matricule']))    $erreurs[] = 'Matricule obligatoire';
            if (empty($data['type']))         $erreurs[] = 'Type obligatoire';
            elseif (!in_array($data['type'], ['CDI', 'CDD', 'interim', 'vacataire'])) $erreurs[] = 'Type invalide (CDI/CDD/interim/vacataire)';
            if (empty($data['date_debut']))   $erreurs[] = 'Date début obligatoire';
            elseif (!$this->dateValide($data['date_debut'])) $erreurs[] = 'Date début invalide (JJ/MM/AAAA)';
            if (empty($data['salaire_base'])) $erreurs[] = 'Salaire obligatoire';
            elseif (!is_numeric($data['salaire_base']) || (float)$data['salaire_base'] <= 0) $erreurs[] = 'Salaire doit être > 0';
            if (!empty($data['matricule']) && !Employe::where('matricule', $data['matricule'])->exists()) $erreurs[] = "Matricule {$data['matricule']} introuvable";
            if (!empty($data['date_fin']) && !$this->dateValide($data['date_fin'])) $erreurs[] = 'Date fin invalide (JJ/MM/AAAA)';
            if (in_array($data['type'] ?? '', ['CDD', 'interim', 'vacataire']) && empty($data['date_fin'])) $erreurs[] = 'Date fin obligatoire pour CDD/intérim/vacataire';
        }

        return $erreurs;
    }

    public function dateValide(string $val): bool
    {
        $d = \DateTime::createFromFormat('d/m/Y', $val);
        return $d && $d->format('d/m/Y') === $val;
    }

    public function parseDate(string $val): string
    {
        return \DateTime::createFromFormat('d/m/Y', $val)->format('Y-m-d');
    }

    public function insererEmploye(array $ligne, CodificationService $svc): void
    {
        $uniteId = !empty($ligne['unite'])
            ? UniteOrganisationnelle::where('nom', $ligne['unite'])->value('id')
            : null;

        Employe::create([
            'matricule'           => $svc->generer('matricule'),
            'nom'                 => strtoupper(trim($ligne['nom'])),
            'prenom'              => ucwords(strtolower(trim($ligne['prenom']))),
            'cin'                 => strtoupper(trim($ligne['cin'])),
            'sexe'                => !empty($ligne['sexe']) ? strtoupper($ligne['sexe']) : null,
            'date_naissance'      => !empty($ligne['date_naissance']) ? $this->parseDate($ligne['date_naissance']) : null,
            'date_embauche'       => $this->parseDate($ligne['date_embauche']),
            'email'               => !empty($ligne['email']) ? strtolower(trim($ligne['email'])) : null,
            'telephone'           => $ligne['telephone'] ?: null,
            'situation_familiale' => $ligne['situation_familiale'] ?: null,
            'nombre_enfants'      => is_numeric($ligne['nombre_enfants']) ? (int)$ligne['nombre_enfants'] : 0,
            'unite_id'            => $uniteId,
            'statut'              => 'actif',
            'quotite_travail'     => 100,
        ]);
    }

    public function insererContrat(array $ligne, CodificationService $svc): void
    {
        $employe = Employe::where('matricule', $ligne['matricule'])->firstOrFail();

        Contrat::create([
            'employe_id'   => $employe->id,
            'reference'    => $svc->generer('contrat'),
            'type'         => $ligne['type'],
            'date_debut'   => $this->parseDate($ligne['date_debut']),
            'date_fin'     => !empty($ligne['date_fin']) ? $this->parseDate($ligne['date_fin']) : null,
            'salaire_base' => (float)$ligne['salaire_base'],
            'statut'       => 'en_cours',
        ]);
    }
}
