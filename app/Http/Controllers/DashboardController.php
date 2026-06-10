<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BulletinPaie;
use App\Models\Conge;
use App\Models\Contrat;
use App\Models\Employe;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats personnel ───────────────────────────────────────
        $stats = [
            'personnel' => [
                'total'    => Employe::count(),
                'actifs'   => Employe::actifs()->count(),
                'inactifs' => Employe::whereIn('statut', ['inactif', 'suspendu'])->count(),
            ],
            'contrats' => [
                'actifs'   => Contrat::actifs()->count(),
                'expirants'=> Contrat::expirants(30)->count(),
                'cdd'      => Contrat::actifs()->where('type', 'CDD')->count(),
            ],
            'conges' => [
                'en_attente' => Conge::enAttente()->count(),
                'du_mois'    => (int) Conge::approuves()
                                    ->whereYear('date_debut', now()->year)
                                    ->whereMonth('date_debut', now()->month)
                                    ->sum('nb_jours'),
            ],
            'paie' => [
                'bulletins_mois'    => BulletinPaie::where('periode_mois',  now()->month)
                                           ->where('periode_annee', now()->year)
                                           ->count(),
                'masse_mois'        => (float) BulletinPaie::where('statut', 'paye')
                                           ->where('periode_mois',  now()->month)
                                           ->where('periode_annee', now()->year)
                                           ->sum('net_a_payer'),
                'bulletins_valides' => BulletinPaie::where('statut', 'valide')->count(),
            ],
        ];

        // ── Contrats qui expirent dans 30 jours ───────────────────
        $contratsExpirants = Contrat::with('employe')
            ->expirants(30)
            ->orderBy('date_fin')
            ->limit(5)
            ->get();

        // ── Congés en attente d'approbation ───────────────────────
        $congesAttente = Conge::with('employe')
            ->enAttente()
            ->latest()
            ->limit(5)
            ->get();

        // ── Dernières embauches ────────────────────────────────────
        $dernierPersonnel = Employe::actifs()
            ->with('fichePoste')
            ->orderByDesc('date_embauche')
            ->limit(5)
            ->get();

        // ── Activité récente (audit) ───────────────────────────────
        $recentActivites = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'stats',
            'contratsExpirants',
            'congesAttente',
            'dernierPersonnel',
            'recentActivites'
        ));
    }
}
