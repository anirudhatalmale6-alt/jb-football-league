<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\StandingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create')->middleware('auth');
Route::get('/competitions/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');

Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create')->middleware('auth');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');

Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create')->middleware('auth');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create')->middleware('auth');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');

Route::get('/standings', [StandingController::class, 'index'])->name('standings.index');

Route::get('/matches/{match}/pdf/summary', [PdfController::class, 'matchSummary'])->name('matches.pdf.summary');
Route::get('/matches/{match}/pdf/teamsheet', [PdfController::class, 'teamSheet'])->name('matches.pdf.teamsheet');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/competitions', [CompetitionController::class, 'store'])->name('competitions.store');
    Route::get('/competitions/{competition}/edit', [CompetitionController::class, 'edit'])->name('competitions.edit');
    Route::put('/competitions/{competition}', [CompetitionController::class, 'update'])->name('competitions.update');
    Route::delete('/competitions/{competition}', [CompetitionController::class, 'destroy'])->name('competitions.destroy');
    Route::post('/competitions/{competition}/groups', [CompetitionController::class, 'storeGroup'])->name('competitions.groups.store');
    Route::delete('/competitions/{competition}/groups/{group}', [CompetitionController::class, 'deleteGroup'])->name('competitions.groups.destroy');

    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/approve', [TeamController::class, 'approve'])->name('teams.approve');

    Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
    Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
    Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');
    Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');

    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::get('/matches/{match}/edit', [MatchController::class, 'edit'])->name('matches.edit');
    Route::put('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');
    Route::get('/matches/{match}/lineup', [MatchController::class, 'lineup'])->name('matches.lineup');
    Route::post('/matches/{match}/lineup', [MatchController::class, 'storeLineup'])->name('matches.lineup.store');
    Route::delete('/matches/{match}/lineup/{lineup}', [MatchController::class, 'deleteLineup'])->name('matches.lineup.destroy');
    Route::get('/matches/{match}/events', [MatchController::class, 'events'])->name('matches.events');
    Route::post('/matches/{match}/events', [MatchController::class, 'storeEvent'])->name('matches.events.store');
    Route::delete('/matches/{match}/events/{event}', [MatchController::class, 'deleteEvent'])->name('matches.events.destroy');
    Route::post('/matches/{match}/complete', [MatchController::class, 'complete'])->name('matches.complete');

    Route::post('/standings/recalculate/{competition}', [StandingController::class, 'recalculate'])->name('standings.recalculate');
});
