<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StandingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\DisciplinaryController;
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

// Team Registration (public browsing, auth required to register)
Route::get('/register-team', [RegistrationController::class, 'index'])->name('registration.index');
Route::get('/register-team/{competition}', [RegistrationController::class, 'create'])->name('registration.create')->middleware('auth');
Route::post('/register-team/{competition}', [RegistrationController::class, 'store'])->name('registration.store')->middleware('auth');
Route::get('/registration/success', [RegistrationController::class, 'success'])->name('registration.success');
Route::get('/payment/return', [RegistrationController::class, 'paymentReturn'])->name('payment.return');
Route::post('/payment/callback', [RegistrationController::class, 'paymentCallback'])->name('payment.callback');

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

    Route::get('/officials/create/{team}', [OfficialController::class, 'create'])->name('officials.create');
    Route::post('/officials', [OfficialController::class, 'store'])->name('officials.store');
    Route::get('/officials/{official}/edit', [OfficialController::class, 'edit'])->name('officials.edit');
    Route::put('/officials/{official}', [OfficialController::class, 'update'])->name('officials.update');
    Route::delete('/officials/{official}', [OfficialController::class, 'destroy'])->name('officials.destroy');

    Route::get('/my-payments', [RegistrationController::class, 'myPayments'])->name('my.payments');
    Route::get('/admin/payments', [RegistrationController::class, 'adminPayments'])->name('admin.payments');
    Route::post('/admin/payments/{payment}/mark-paid', [RegistrationController::class, 'markAsPaid'])->name('admin.payments.mark-paid');
    Route::get('/payments/{payment}/receipt', [PdfController::class, 'paymentReceipt'])->name('payments.receipt');

    // Disciplinary Fines
    Route::get('/disciplinary', [DisciplinaryController::class, 'index'])->name('disciplinary.index');
    Route::get('/disciplinary/create', [DisciplinaryController::class, 'create'])->name('disciplinary.create');
    Route::post('/disciplinary', [DisciplinaryController::class, 'store'])->name('disciplinary.store');
    Route::post('/disciplinary/{fine}/mark-paid', [DisciplinaryController::class, 'markAsPaid'])->name('disciplinary.mark-paid');
    Route::post('/disciplinary/{fine}/waive', [DisciplinaryController::class, 'waive'])->name('disciplinary.waive');
    Route::post('/disciplinary/{fine}/lift-suspension', [DisciplinaryController::class, 'liftSuspension'])->name('disciplinary.lift-suspension');
    Route::post('/disciplinary/{fine}/matches-served', [DisciplinaryController::class, 'updateMatchesServed'])->name('disciplinary.matches-served');
    Route::post('/disciplinary/{fine}/upload-proof', [DisciplinaryController::class, 'uploadProof'])->name('disciplinary.upload-proof');
    Route::get('/disciplinary/{fine}/proof', [DisciplinaryController::class, 'viewProof'])->name('disciplinary.view-proof');
    Route::delete('/disciplinary/{fine}', [DisciplinaryController::class, 'destroy'])->name('disciplinary.destroy');
    Route::get('/my-fines', [DisciplinaryController::class, 'myFines'])->name('my.fines');
    Route::get('/disciplinary/{fine}/receipt', [PdfController::class, 'fineReceipt'])->name('disciplinary.receipt');

    // API endpoints for AJAX
    Route::get('/api/teams/{team}/players', [DisciplinaryController::class, 'getPlayers'])->name('api.team.players');
    Route::get('/api/competitions/{competition}/matches', [DisciplinaryController::class, 'getMatches'])->name('api.competition.matches');
});
