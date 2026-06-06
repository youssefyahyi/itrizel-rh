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
    Route::resource('paie',        PaieController::class)->parameters(['paie' => 'paie']);
    Route::resource('evaluations', EvaluationController::class)->parameters(['evaluations' => 'evaluation']);
    Route::resource('formations',  FormationController::class)->parameters(['formations' => 'formation']);
    Route::resource('documents',   DocumentController::class);

    // Paramétrage
    Route::prefix('parametrage')->name('parametrage.')->group(function () {
        Route::get('/', [ParametrageController::class, 'index'])->name('index');

        // Référentiel Postes
        Route::resource('postes', PosteController::class)->parameters(['postes' => 'poste']);
        Route::patch('postes/{poste}/toggle', [PosteController::class, 'toggle'])->name('postes.toggle');

        // Organisation
        Route::get('organisation',          [OrganisationController::class, 'index'])->name('organisation.index');
        Route::post('organisation',         [OrganisationController::class, 'store'])->name('organisation.store');
        Route::get('organisation/{unite}',  [OrganisationController::class, 'show'])->name('organisation.show');
        Route::put('organisation/{unite}',  [OrganisationController::class, 'update'])->name('organisation.update');
        Route::delete('organisation/{unite}',[OrganisationController::class, 'destroy'])->name('organisation.destroy');
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
