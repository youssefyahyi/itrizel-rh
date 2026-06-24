<?php

namespace App\Http\Controllers;

use App\Services\{CodificationService, ImportService};
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

    public function __construct(private ImportService $svc) {}

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

        $colonnes  = ImportService::$colonnes[$entite];
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
        $colonnes = array_column(ImportService::$colonnes[$entite], 'col');
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

            $erreurs        = $this->svc->valider($data, $entite);
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
        $colonnes = ImportService::$colonnes[$entite];
        return view('outils.import.preview', array_merge(compact('entite', 'colonnes'), $data));
    }

    public function importer(Request $request, string $entite)
    {
        abort_unless(in_array($entite, self::ENTITES), 404);
        $data = session("import_{$entite}");
        if (!$data) return redirect()->route('outils.import.charger', $entite);

        $valides  = array_filter($data['lignes'], fn($l) => $l['_statut'] === 'valide');
        $codif    = app(CodificationService::class);
        $importes = 0;
        $erreurs  = [];

        DB::transaction(function () use ($valides, $entite, $codif, &$importes, &$erreurs) {
            foreach ($valides as $ligne) {
                try {
                    $entite === 'employes'
                        ? $this->svc->insererEmploye($ligne, $codif)
                        : $this->svc->insererContrat($ligne, $codif);
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

}
