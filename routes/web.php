<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\PaieController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ParametrageController;
use App\Http\Controllers\ParametrageContratController;
use App\Http\Controllers\AvenantController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ReferentielEmploisController;
use App\Http\Controllers\CategorieEmployeController;
use App\Http\Controllers\FonctionController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard — redirige les salariés vers leur fiche
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modules RH
    // ── Modules accessibles aux salariés (avec scope) ─────────────────
    Route::resource('personnel',   PersonnelController::class)->parameters(['personnel' => 'employe']);
    Route::resource('conges',      CongeController::class)->parameters(['conges' => 'conge']);
    Route::resource('evaluations', EvaluationController::class)->parameters(['evaluations' => 'evaluation']);
    Route::resource('formations',  FormationController::class)->parameters(['formations' => 'formation']);

    // ── Modules interdits aux salariés ─────────────────────────────────
    Route::middleware('not.salarie')->group(function () {
        Route::patch('personnel/{employe}/toggle-statut', [PersonnelController::class, 'toggleStatut'])->name('personnel.toggle-statut');
        Route::resource('contrats', ContratController::class)->parameters(['contrats' => 'contrat']);
        Route::get('contrats/{contrat}/renouveler',        [ContratController::class, 'renouveler'])->name('contrats.renouveler');
        Route::post('contrats/{contrat}/renouveler',       [ContratController::class, 'storeRenouvellement'])->name('contrats.store-renouvellement');
        Route::post('contrats/{contrat}/pdf',              [ContratController::class, 'genererPdf'])->name('contrats.pdf');
        Route::get('contrats/{contrat}/avenants/create',   [AvenantController::class, 'create'])->name('avenants.create');
        Route::post('contrats/{contrat}/avenants',         [AvenantController::class, 'store'])->name('avenants.store');
        Route::get('avenants/{avenant}',                   [AvenantController::class, 'show'])->name('avenants.show');
        Route::post('avenants/{avenant}/pdf',              [AvenantController::class, 'genererPdf'])->name('avenants.pdf');
        Route::resource('absences', AbsenceController::class)->parameters(['absences' => 'absence']);
        Route::patch('conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('conges.approuver');
        Route::patch('conges/{conge}/rejeter',   [CongeController::class, 'rejeter'])->name('conges.rejeter');
        Route::get('paie/livre',       [PaieController::class, 'livre'])->name('paie.livre');
        Route::get('paie/bordereau',   [PaieController::class, 'bordereau'])->name('paie.bordereau');
        Route::get('paie/das',         [PaieController::class, 'das'])->name('paie.das');
        Route::resource('paie',        PaieController::class)->parameters(['paie' => 'paie']);
        Route::get('paie/{paie}/print',[PaieController::class, 'print'])->name('paie.print');
        Route::get('paie-taux',        [PaieController::class, 'taux'])->name('paie.taux');
        Route::resource('documents',   DocumentController::class);
    });

    // Paramétrage — interdit aux salariés
    Route::middleware('not.salarie')->prefix('parametrage')->name('parametrage.')->group(function () {
        Route::get('/',            [ParametrageController::class, 'index'])->name('index');
        Route::get('paie',         [ParametrageController::class, 'paie'])->name('paie');
        Route::post('paie',        [ParametrageController::class, 'updatePaie'])->name('paie.update');
        Route::get('rh',                            [ParametrageController::class, 'rh'])->name('rh');
        Route::post('rh',                           [ParametrageController::class, 'updateRh'])->name('rh.update');
        Route::post('jours-feries',                 [ParametrageController::class, 'storeJourFerie'])->name('jours-feries.store');
        Route::delete('jours-feries/{jourFerie}',   [ParametrageController::class, 'destroyJourFerie'])->name('jours-feries.destroy');

        // Paramétrage contrats (bibliothèque de clauses)
        Route::get('contrats',                          [ParametrageContratController::class, 'index'])->name('contrats.index');
        Route::post('contrats',                         [ParametrageContratController::class, 'store'])->name('contrats.store');
        Route::put('contrats/{clause}',                 [ParametrageContratController::class, 'update'])->name('contrats.update');
        Route::delete('contrats/{clause}',              [ParametrageContratController::class, 'destroy'])->name('contrats.destroy');
        Route::patch('contrats/{clause}/monter',        [ParametrageContratController::class, 'monter'])->name('contrats.monter');
        Route::patch('contrats/{clause}/descendre',     [ParametrageContratController::class, 'descendre'])->name('contrats.descendre');

        // Organisation
        Route::get('organisation',           [OrganisationController::class, 'index'])->name('organisation.index');
        Route::post('organisation',          [OrganisationController::class, 'store'])->name('organisation.store');
        Route::get('organisation/{unite}',   [OrganisationController::class, 'show'])->name('organisation.show');
        Route::put('organisation/{unite}',   [OrganisationController::class, 'update'])->name('organisation.update');
        Route::delete('organisation/{unite}',[OrganisationController::class, 'destroy'])->name('organisation.destroy');

        // Référentiel des emplois — fiches d'emploi
        Route::get('emplois',              [ReferentielEmploisController::class, 'index'])->name('emplois.index');
        Route::post('emplois',             [ReferentielEmploisController::class, 'store'])->name('emplois.store');
        Route::get('emplois/{fiche}',      [ReferentielEmploisController::class, 'show'])->name('emplois.show');
        Route::put('emplois/{fiche}',      [ReferentielEmploisController::class, 'update'])->name('emplois.update');
        Route::delete('emplois/{fiche}',   [ReferentielEmploisController::class, 'destroy'])->name('emplois.destroy');
        Route::patch('emplois/{fiche}/toggle', [ReferentielEmploisController::class, 'toggle'])->name('emplois.toggle');

        // Labels — Catégories
        Route::post('emplois/categories',                          [CategorieEmployeController::class, 'store'])->name('emplois.categories.store');
        Route::put('emplois/categories/{categorie}',               [CategorieEmployeController::class, 'update'])->name('emplois.categories.update');
        Route::delete('emplois/categories/{categorie}',            [CategorieEmployeController::class, 'destroy'])->name('emplois.categories.destroy');
        Route::patch('emplois/categories/{categorie}/toggle',      [CategorieEmployeController::class, 'toggle'])->name('emplois.categories.toggle');

        // Labels — Fonctions
        Route::post('emplois/fonctions',                           [FonctionController::class, 'store'])->name('emplois.fonctions.store');
        Route::put('emplois/fonctions/{fonction}',                 [FonctionController::class, 'update'])->name('emplois.fonctions.update');
        Route::delete('emplois/fonctions/{fonction}',              [FonctionController::class, 'destroy'])->name('emplois.fonctions.destroy');
        Route::patch('emplois/fonctions/{fonction}/toggle',        [FonctionController::class, 'toggle'])->name('emplois.fonctions.toggle');

        // Labels — Postes
        Route::post('emplois/postes',                              [PosteController::class, 'store'])->name('emplois.postes.store');
        Route::put('emplois/postes/{poste}',                       [PosteController::class, 'update'])->name('emplois.postes.update');
        Route::delete('emplois/postes/{poste}',                    [PosteController::class, 'destroy'])->name('emplois.postes.destroy');
        Route::patch('emplois/postes/{poste}/toggle',              [PosteController::class, 'toggle'])->name('emplois.postes.toggle');
    });

    // Profil
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Administration — admin uniquement
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });
});

require __DIR__.'/auth.php';
