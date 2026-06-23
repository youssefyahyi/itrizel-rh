<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasFicheData;
use App\Http\Requests\StoreEmployeRequest;
use App\Http\Requests\UpdateEmployeRequest;
use App\Models\AuditLog;
use App\Models\CategorieEmploye;
use App\Models\Employe;
use App\Models\UniteOrganisationnelle;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    use HasFicheData;

    private function abortIfSalarie(): void
    {
        if (auth()->user()->isSalarie()) abort(403);
    }

    private function scopeSalarie(Employe $employe): void
    {
        $user = auth()->user();
        if ($user->isSalarie() && $user->employe_id !== $employe->id) {
            abort(403);
        }
    }

    // ── Liste ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->abortIfSalarie();
        $query = Employe::with(["contratActif", "fichePoste.categorie", "unite"])->orderBy("nom");

        if ($request->filled("q")) {
            $query->where(function ($q) use ($request) {
                $q->where("nom", "like", "%{$request->q}%")
                  ->orWhere("prenom", "like", "%{$request->q}%")
                  ->orWhere("matricule", "like", "%{$request->q}%")
                  ->orWhere("cin", "like", "%{$request->q}%");
            });
        }

        if ($request->filled("statut")) {
            $query->where("statut", $request->statut);
        }

        if ($request->filled("unite_id")) {
            $query->where("unite_id", $request->unite_id);
        }

        if ($request->filled("categorie_id")) {
            $query->whereHas("fichePoste", fn($q) =>
                $q->where("categorie_id", $request->categorie_id)
            );
        }

        if ($request->filled("type_contrat")) {
            $query->whereHas("contratActif", fn($q) =>
                $q->where("type", $request->type_contrat)
            );
        }

        if ($request->filled("anciennete")) {
            match ($request->anciennete) {
                "lt1"   => $query->where("date_embauche", ">=", now()->subYear()),
                "1a5"   => $query->whereBetween("date_embauche", [now()->subYears(5), now()->subYear()]),
                "5a10"  => $query->whereBetween("date_embauche", [now()->subYears(10), now()->subYears(5)]),
                "gt10"  => $query->where("date_embauche", "<=", now()->subYears(10)),
                default => null,
            };
        }

        if ($request->filled("sans_contrat")) {
            $query->whereDoesntHave("contratActif");
        }

        $employes = $query->paginate(20)->withQueryString();

        $stats = [
            "total"         => Employe::count(),
            "actifs"        => Employe::actifs()->count(),
            "inactifs"      => Employe::whereIn("statut", ["inactif", "suspendu"])->count(),
            "expires"       => Employe::whereHas("contratActif", fn($q) =>
                                   $q->whereNotNull("date_fin")
                                    ->whereBetween("date_fin", [now(), now()->addDays(30)])
                               )->count(),
            "sans_contrat"  => Employe::actifs()->whereDoesntHave("contratActif")->count(),
        ];

        [$fiches, $categories] = $this->ficheData();

        $unites      = UniteOrganisationnelle::orderBy("nom")->get(["id", "nom", "type"]);
        $filtreCategories = CategorieEmploye::orderBy("nom")->get(["id", "nom"]);

        return view("personnel.index", compact("employes", "stats", "fiches", "categories", "unites", "filtreCategories"));
    }

    // ── Formulaire création ────────────────────────────────────────
    public function create()
    {
        $this->abortIfSalarie();
        [$fiches, $categories] = $this->ficheData();
        return view("personnel.form", ["employe" => new Employe, "mode" => "create",
            "fiches" => $fiches, "categories" => $categories]);
    }

    // ── Enregistrement ─────────────────────────────────────────────
    public function store(StoreEmployeRequest $request)
    {
        $this->abortIfSalarie();
        $data = $request->validated();
        $data["created_by"]  = auth()->id();
        $data["matricule"]   = Employe::generateMatricule();

        if ($request->hasFile("photo")) {
            $data["photo"] = $request->file("photo")->store("employes/photos", "public");
        }

        $employe = Employe::create($data);

        AuditLog::log("Personnel", "creation", "Création de l employé {$employe->nom_complet} ({$employe->matricule})", $employe);

        return redirect()
            ->route("personnel.show", $employe)
            ->with("success", "Employé {$employe->nom_complet} créé avec succès.");
    }

    // ── Détail ─────────────────────────────────────────────────────
    public function show(Employe $employe)
    {
        $this->scopeSalarie($employe);
        $employe->load([
            "fichePoste",
            "contrats"      => fn($q) => $q->latest(),
            "conges"        => fn($q) => $q->latest()->limit(5),
            "bulletinsPaie" => fn($q) => $q->latest()->limit(5),
        ]);
        $auditLogs  = AuditLog::where("module", "Personnel")
                        ->where("description", "like", "%{$employe->matricule}%")
                        ->latest()->limit(20)->with("user")->get();
        $isSalarie  = auth()->user()->isSalarie();
        return view("personnel.show", compact("employe", "auditLogs", "isSalarie"));
    }

    // ── Formulaire édition ─────────────────────────────────────────
    public function edit(Employe $employe)
    {
        $this->abortIfSalarie();
        [$fiches, $categories] = $this->ficheData();
        return view("personnel.form", ["employe" => $employe, "mode" => "edit",
            "fiches" => $fiches, "categories" => $categories]);
    }

    // ── Mise à jour ────────────────────────────────────────────────
    public function update(UpdateEmployeRequest $request, Employe $employe)
    {
        $this->abortIfSalarie();
        $data = $request->validated();

        if ($request->hasFile("photo")) {
            $data["photo"] = $request->file("photo")->store("employes/photos", "public");
        }

        $employe->update($data);

        AuditLog::log("Personnel", "modification", "Modification de l employé {$employe->nom_complet}", $employe);

        return redirect()
            ->route("personnel.show", $employe)
            ->with("success", "Employé mis à jour.");
    }

    // ── Suppression (soft delete) ──────────────────────────────────
    public function destroy(Employe $employe)
    {
        $this->abortIfSalarie();
        AuditLog::log("Personnel", "suppression", "Suppression de l employé {$employe->nom_complet}", $employe);
        $employe->delete();

        return redirect()
            ->route("personnel.index")
            ->with("success", "Employé archivé.");
    }

    // ── Changement de statut ───────────────────────────────────────
    public function toggleStatut(Employe $employe)
    {
        $statut = $employe->statut === "actif" ? "inactif" : "actif";
        $employe->update(["statut" => $statut]);

        AuditLog::log("Personnel", "statut", "Statut de {$employe->nom_complet} changé en {$statut}", $employe);

        return back()->with("success", "Statut mis à jour.");
    }

}
