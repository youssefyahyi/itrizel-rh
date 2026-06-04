<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\PaieController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ParametrageController;
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
    Route::resource('presences',   PresenceController::class)->parameters(['presences' => 'presence']);
    Route::resource('conges',      CongeController::class)->parameters(['conges' => 'conge']);
    Route::patch('conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('conges.approuver');
    Route::patch('conges/{conge}/rejeter',   [CongeController::class, 'rejeter'])->name('conges.rejeter');
    Route::resource('paie',        PaieController::class)->parameters(['paie' => 'paie']);
    Route::resource('evaluations', EvaluationController::class)->parameters(['evaluations' => 'evaluation']);
    Route::resource('formations',  FormationController::class)->parameters(['formations' => 'formation']);
    Route::resource('documents',   DocumentController::class);
    Route::resource('parametrage', ParametrageController::class);

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
