<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats RH — seront alimentées au fur et a mesure du dev des modules
        $stats = [
            "personnel" => ["actifs" => 0, "total" => 0],
            "contrats"  => ["actifs" => 0, "expirants" => 0],
            "conges"    => ["en_attente" => 0],
            "paie"      => ["bulletins_mois" => 0],
        ];

        $contratsExpirants  = collect();
        $congesAttente      = collect();
        $dernierPersonnel   = collect();
        $recentActivites    = collect();

        return view("dashboard", compact(
            "stats", "contratsExpirants", "congesAttente",
            "dernierPersonnel", "recentActivites"
        ));
    }
}