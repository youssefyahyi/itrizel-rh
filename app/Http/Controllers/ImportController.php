<?php

namespace App\Http\Controllers;

use App\Models\{Employe, Contrat, UniteOrganisationnelle};
use App\Services\CodificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Fill};
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ImportController extends Controller
{
    private const ENTITES = ['employes', 'contrats'];

    private const COLONNES = [
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

    public function index()
    {
        return view('outils.import.index');
    }

    public function charger(string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);
        return view('outils.import.charger', compact('entite'));
    }

    public function modele(string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);

        $colonnes  = self::COLONNES[$entite];
        $nbCols    = count($colonnes);
        $lastCol   = Coordinate::stringFromColumnIndex($nbCols);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Importation');

        // Ligne 1 — Bandeau instruction
        $sheet->mergeCells("A1:{$lastCol}1");
        $libelle = $entite === 'employes' ? 'Employés' : 'Contrats';
        $sheet->setCellValue('A1', "Modèle import {$libelle} — Saisir à partir de la ligne 4. Colonnes * obligatoires. Ne pas modifier les lignes 1 à 3.");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E5FA3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Ligne 2 — En-têtes colonnes
        foreach ($colonnes as $i => $col) {
            $cell = Coordinate::stringFromColumnIndex($i + 1) . '2';
            $sheet->setCellValue($cell, $col['libelle']);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $col['obligatoire'] ? '374151' : '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            ]);
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth(26);
        }
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Ligne 3 — Exemple (en italique grisé)
        foreach ($colonnes as $i => $col) {
            $cell = Coordinate::stringFromColumnIndex($i + 1) . '3';
            $sheet->setCellValue($cell, $col['exemple']);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ]);
        }
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Zone de données lignes 4-103
        $sheet->getStyle("A4:{$lastCol}103")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
        ]);

        $sheet->freezePane('A4');

        $filename = "modele_import_{$entite}_" . date('Ymd') . ".xlsx";
        $writer   = new XlsxWriter($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function upload(Request $request, string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);
        $request->validate(['fichier' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);

        $path = $request->file('fichier')->store('imports/temp');

        try {
            $spreadsheet = IOFactory::load(Storage::path($path));
        } catch (\Exception) {
            Storage::delete($path);
            return back()->withErrors(['fichier' => 'Impossible de lire le fichier Excel.']);
        }

        $sheet   = $spreadsheet->getActiveSheet();
        $colonnes = array_column(self::COLONNES[$entite], 'col');
        $lignes   = [];

        // Lignes 1-3 = bandeau + en-têtes + exemple → on commence à la ligne 4
        for ($row = 4; $row <= $sheet->getHighestRow(); $row++) {
            $data = [];
            $vide = true;
            foreach ($colonnes as $i => $col) {
                $ref = Coordinate::stringFromColumnIndex($i + 1) . $row;
                $val = trim((string) $sheet->getCell($ref)->getCalculatedValue());
                $data[$col] = $val;
                if ($val !== '') $vide = false;
            }
            if ($vide) continue;

            $erreurs        = $this->valider($data, $entite);
            $data['_ligne'] = $row - 3;
            $data['_statut'] = empty($erreurs) ? 'valide' : 'erreur';
            $data['_erreurs'] = $erreurs;
            $lignes[] = $data;
        }

        Storage::delete($path);

        $stats = [
            'total'   => count($lignes),
            'valides' => count(array_filter($lignes, fn($l) => $l['_statut'] === 'valide')),
            'erreurs' => count(array_filter($lignes, fn($l) => $l['_statut'] === 'erreur')),
        ];

        session()->put("import_{$entite}", compact('lignes', 'stats'));

        return redirect()->route('outils.import.preview', $entite);
    }

    public function preview(string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);
        $data = session("import_{$entite}");
        if (!$data) {
            return redirect()->route('outils.import.charger', $entite)
                ->withErrors(['fichier' => 'Session expirée, veuillez re-uploader.']);
        }
        $colonnes = self::COLONNES[$entite];
        return view('outils.import.preview', array_merge(compact('entite', 'colonnes'), $data));
    }

    public function importer(Request $request, string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);
        $data = session("import_{$entite}");
        if (!$data) return redirect()->route('outils.import.charger', $entite);

        $valides  = array_filter($data['lignes'], fn($l) => $l['_statut'] === 'valide');
        $svc      = app(CodificationService::class);
        $importes = 0;
        $erreurs  = [];

        DB::transaction(function () use ($valides, $entite, $svc, &$importes, &$erreurs) {
            foreach ($valides as $ligne) {
                try {
                    $entite === 'employes'
                        ? $this->insererEmploye($ligne, $svc)
                        : $this->insererContrat($ligne, $svc);
                    $importes++;
                } catch (\Exception $e) {
                    $erreurs[] = "Ligne {$ligne['_ligne']} : " . $e->getMessage();
                }
            }
        });

        session()->forget("import_{$entite}");
        session()->put("import_{$entite}_resultat", compact('importes', 'erreurs'));

        return redirect()->route('outils.import.resultat', $entite);
    }

    public function resultat(string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);
        $res = session("import_{$entite}_resultat", ['importes' => 0, 'erreurs' => []]);
        session()->forget("import_{$entite}_resultat");
        return view('outils.import.resultat', array_merge(compact('entite'), $res));
    }

    // ── Validation ──────────────────────────────────────────────────────
    private function valider(array $data, string $entite): array
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

    private function dateValide(string $val): bool
    {
        $d = \DateTime::createFromFormat('d/m/Y', $val);
        return $d && $d->format('d/m/Y') === $val;
    }

    private function parseDate(string $val): string
    {
        return \DateTime::createFromFormat('d/m/Y', $val)->format('Y-m-d');
    }

    // ── Insertions ──────────────────────────────────────────────────────
    private function insererEmploye(array $ligne, CodificationService $svc): void
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

    private function insererContrat(array $ligne, CodificationService $svc): void
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
