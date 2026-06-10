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
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ReferentielEmploisController;
use App\Http\Controllers\CategorieEmployeController;
use App\Http\Controllers\FonctionController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modules RH
    Route::resource('personnel', PersonnelController::class)->parameters(['personnel' => 'employe']);
    Route::patch('personnel/{employe}/toggle-statut', [PersonnelController::class, 'toggleStatut'])->name('personnel.toggle-statut');
    Route::resource('contrats',    ContratController::class)->parameters(['contrats' => 'contrat']);
    Route::resource('absences',    AbsenceController::class)->parameters(['absences' => 'absence']);
    Route::resource('conges',      CongeController::class)->parameters(['conges' => 'conge']);
    Route::patch('conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('conges.approuver');
    Route::patch('conges/{conge}/rejeter',   [CongeController::class, 'rejeter'])->name('conges.rejeter');
    Route::get('paie/livre',       [PaieController::class, 'livre'])->name('paie.livre');
    Route::get('paie/bordereau',   [PaieController::class, 'bordereau'])->name('paie.bordereau');
    Route::get('paie/das',         [PaieController::class, 'das'])->name('paie.das');
    Route::resource('paie',        PaieController::class)->parameters(['paie' => 'paie']);
    Route::get('paie/{paie}/print', [PaieController::class, 'print'])->name('paie.print');
    Route::get('paie-taux',         [PaieController::class, 'taux'])->name('paie.taux');
    Route::resource('evaluations', EvaluationController::class)->parameters(['evaluations' => 'evaluation']);
    Route::resource('formations',  FormationController::class)->parameters(['formations' => 'formation']);
    Route::resource('documents',   DocumentController::class);

    // Paramétrage
    Route::prefix('parametrage')->name('parametrage.')->group(function () {
        Route::get('/',            [ParametrageController::class, 'index'])->name('index');
        Route::get('paie',         [ParametrageController::class, 'paie'])->name('paie');
        Route::post('paie',        [ParametrageController::class, 'updatePaie'])->name('paie.update');

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

    // Administration
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('teams', TeamController::class);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });
});

require __DIR__.'/auth.php';
