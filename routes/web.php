<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\KnockoutController;
use App\Http\Controllers\TopScorerController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StandingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\DisciplinaryController;
use App\Http\Controllers\AffiliateFeeController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\LineupSubmissionController;
use App\Http\Controllers\JerseySubmissionController;
use App\Http\Controllers\MatchDayPhotoController;
use App\Http\Controllers\McAssignmentController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SubstitutionRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('/top-scorers', [TopScorerController::class, 'index'])->name('top-scorers');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset (guest, self-service). Uses Laravel's Password broker:
// unique single-use token, 60-minute expiry, invalidated after reset.
Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Email Verification
Route::get("/email/verify", [VerificationController::class, "notice"])->middleware("auth")->name("verification.notice");
Route::get("/email/verify/{id}/{hash}", [VerificationController::class, "verify"])->middleware(["auth", "signed"])->name("verification.verify");
Route::post("/email/verification-notification", [VerificationController::class, "send"])->middleware(["auth", "throttle:6,1"])->name("verification.send");

Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create')->middleware(['auth', 'verified']);
Route::get('/competitions/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');

Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create')->middleware(['auth', 'verified']);
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');

Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create')->middleware(['auth', 'verified']);
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create')->middleware(['auth', 'verified']);
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');

Route::get('/standings', [StandingController::class, 'index'])->name('standings.index');

Route::get('/matches/{match}/pdf/summary', [PdfController::class, 'matchSummary'])->name('matches.pdf.summary');
Route::get('/matches/{match}/pdf/teamsheet', [PdfController::class, 'teamSheet'])->name('matches.pdf.teamsheet');

// Team Registration (public browsing, auth required to register)
Route::get('/register-team', [RegistrationController::class, 'index'])->name('registration.index');
Route::get('/register-team/{competition}', [RegistrationController::class, 'create'])->name('registration.create')->middleware(['auth', 'verified']);
Route::post('/register-team/{competition}', [RegistrationController::class, 'store'])->name('registration.store')->middleware(['auth', 'verified']);
Route::get('/registration/success', [RegistrationController::class, 'success'])->name('registration.success');
Route::get('/payment/return', [RegistrationController::class, 'paymentReturn'])->name('payment.return');
Route::post('/payment/callback', [RegistrationController::class, 'paymentCallback'])->name('payment.callback');


// News (public)
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/liga-info', [\App\Http\Controllers\LigaInfoController::class, 'index'])->name('liga-info.index');

Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::post('/teams/{team}/withdraw', [TeamController::class, 'withdraw'])->name('teams.withdraw');
    Route::patch('/teams/{team}/change-status', [TeamController::class, 'changeStatus'])->name('teams.changeStatus');
    Route::post('/teams/{team}/reject', [TeamController::class, 'reject'])->name('teams.reject');
    // Admin: link/unlink a team-manager account to a team (controls player-registration access)
    Route::post('/teams/{team}/managers', [TeamController::class, 'assignManager'])->name('teams.assign-manager');
    Route::delete('/teams/{team}/managers/{user}', [TeamController::class, 'removeManager'])->name('teams.remove-manager');

    Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
    Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
    Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');
    Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');
    Route::patch('/players/{player}/verify', [PlayerController::class, 'verify'])->name('players.verify');

    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::get('/matches/{match}/edit', [MatchController::class, 'edit'])->name('matches.edit');
    Route::put('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');
    // Super Admin: archive (hide, keep records) / restore, plus the deletion audit trail
    Route::post('/matches/{match}/archive', [MatchController::class, 'archive'])->name('matches.archive');
    Route::post('/matches/{match}/restore', [MatchController::class, 'restore'])->name('matches.restore');
    Route::get('/matches-audit-log', [MatchController::class, 'auditLog'])->name('matches.audit-log');
    Route::get('/matches/{match}/lineup', [MatchController::class, 'lineup'])->name('matches.lineup');
    Route::post('/matches/{match}/lineup', [MatchController::class, 'storeLineup'])->name('matches.lineup.store');
    Route::delete('/matches/{match}/lineup/{lineup}', [MatchController::class, 'deleteLineup'])->name('matches.lineup.destroy');
    Route::get('/matches/{match}/events', [MatchController::class, 'events'])->name('matches.events');
    Route::post('/matches/{match}/events', [MatchController::class, 'storeEvent'])->name('matches.events.store');
    Route::delete('/matches/{match}/events/{event}', [MatchController::class, 'deleteEvent'])->name('matches.events.destroy');
    Route::post('/matches/{match}/complete', [MatchController::class, 'complete'])->name('matches.complete');
    Route::post('/matches/{match}/signature', [MatchController::class, 'storeSignature'])->name('matches.signature.store');
    Route::delete('/matches/{match}/signature/{signature}', [MatchController::class, 'deleteSignature'])->name('matches.signature.destroy');
    Route::post('/matches/{match}/remarks', [MatchController::class, 'storeRemarks'])->name('matches.remarks.store');
    Route::post('/matches/{match}/submit-final', [MatchController::class, 'submitFinalReport'])->name('matches.submit-final');
    Route::post('/matches/{match}/unlock', [MatchController::class, 'unlockMatch'])->name('matches.unlock');

    // Substitution requests: Team Manager requests, Match Commissioner approves.
    Route::post('/matches/{match}/substitution-requests', [SubstitutionRequestController::class, 'store'])->name('substitution-requests.store');
    Route::post('/matches/{match}/substitution-requests/{request}/approve', [SubstitutionRequestController::class, 'approve'])->name('substitution-requests.approve');
    Route::post('/matches/{match}/substitution-requests/{request}/reject', [SubstitutionRequestController::class, 'reject'])->name('substitution-requests.reject');

    // Knockout bracket
    Route::get('/competitions/{competition}/knockout', [KnockoutController::class, 'bracket'])->name('knockout.bracket');
    Route::post('/competitions/{competition}/knockout/init', [KnockoutController::class, 'initBracket'])->name('knockout.init');
    Route::post('/competitions/{competition}/knockout/seed', [KnockoutController::class, 'seedTeam'])->name('knockout.seed');
    Route::post('/competitions/{competition}/knockout/{knockoutMatch}/winner', [KnockoutController::class, 'setWinner'])->name('knockout.set-winner');
    Route::delete('/competitions/{competition}/knockout/reset', [KnockoutController::class, 'resetBracket'])->name('knockout.reset');
    Route::post('/competitions/{competition}/knockout/{knockoutMatch}/link', [KnockoutController::class, 'linkMatch'])->name('knockout.link-match');


Route::post('/matches/{match}/update-status', [MatchController::class, 'updateStatus'])->name('matches.update-status');    Route::post('/matches/{match}/update-score', [MatchController::class, 'updateScore'])->name('matches.update-score');

    Route::post('/standings/recalculate/{competition}', [StandingController::class, 'recalculate'])->name('standings.recalculate');

    Route::get('/officials/create/{team}', [OfficialController::class, 'create'])->name('officials.create');
    Route::post('/officials', [OfficialController::class, 'store'])->name('officials.store');
    Route::get('/officials/{official}/edit', [OfficialController::class, 'edit'])->name('officials.edit');
    Route::put('/officials/{official}', [OfficialController::class, 'update'])->name('officials.update');
    Route::delete('/officials/{official}', [OfficialController::class, 'destroy'])->name('officials.destroy');

    Route::get('/my-payments', [RegistrationController::class, 'myPayments'])->name('my.payments');
    Route::get('/admin/payments', [RegistrationController::class, 'adminPayments'])->name('admin.payments');
    Route::post('/admin/payments/{payment}/mark-paid', [RegistrationController::class, 'markAsPaid'])->name('admin.payments.mark-paid');
    Route::post('/admin/payments/sync', [RegistrationController::class, 'syncPayments'])->name('admin.payments.sync');
    Route::get('/payments/{payment}/receipt', [PdfController::class, 'paymentReceipt'])->name('payments.receipt');
    Route::get('/teams/{team}/eligibility-letter', [PdfController::class, 'eligibilityLetter'])->name('teams.eligibility-letter');

    // Disciplinary Fines
    Route::get('/disciplinary', [DisciplinaryController::class, 'index'])->name('disciplinary.index');
    Route::post('/disciplinary/sync', [DisciplinaryController::class, 'sync'])->name('disciplinary.sync');
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

    // Affiliate Membership Fees (RM50 per team)
    Route::get('/affiliate-fees', [AffiliateFeeController::class, 'index'])->name('affiliate-fees.index');
    Route::post('/affiliate-fees/remind-all', [AffiliateFeeController::class, 'remindAll'])->name('affiliate-fees.remind-all');
    Route::post('/affiliate-fees/bulk-mark', [AffiliateFeeController::class, 'bulkMark'])->name('affiliate-fees.bulk-mark');
    Route::post('/affiliate-fees/bulk-remind', [AffiliateFeeController::class, 'bulkRemind'])->name('affiliate-fees.bulk-remind');
    Route::post('/affiliate-fees/bulk-require', [AffiliateFeeController::class, 'bulkRequire'])->name('affiliate-fees.bulk-require');
    Route::post('/affiliate-fees/{team}/mark-paid', [AffiliateFeeController::class, 'markPaid'])->name('affiliate-fees.mark-paid');
    Route::post('/affiliate-fees/{team}/mark-unpaid', [AffiliateFeeController::class, 'markUnpaid'])->name('affiliate-fees.mark-unpaid');
    Route::post('/affiliate-fees/{team}/remind', [AffiliateFeeController::class, 'remind'])->name('affiliate-fees.remind');

    // API endpoints for AJAX
    Route::get('/api/teams/{team}/players', [DisciplinaryController::class, 'getPlayers'])->name('api.team.players');
    Route::get('/api/competitions/{competition}/matches', [DisciplinaryController::class, 'getMatches'])->name('api.competition.matches');

    // Line-Up Submissions
    Route::get("/lineup-submissions", [LineupSubmissionController::class, "index"])->name("lineup-submissions.index");
    Route::get("/lineup-submissions/{match}/{team}/edit", [LineupSubmissionController::class, "edit"])->name("lineup-submissions.edit");
    Route::get("/lineup-submissions/{match}/review", [LineupSubmissionController::class, "review"])->name("lineup-submissions.review");
    Route::post("/lineup-submissions/{match}/{team}", [LineupSubmissionController::class, "store"])->name("lineup-submissions.store");
    Route::get("/lineup-submissions/{match}/{team}", [LineupSubmissionController::class, "show"])->name("lineup-submissions.show");
    Route::post("/lineup-submissions/{match}/{team}/submit", [LineupSubmissionController::class, "submit"])->name("lineup-submissions.submit");
    Route::post("/lineup-submissions/{match}/{team}/approve", [LineupSubmissionController::class, "approve"])->name("lineup-submissions.approve");
    Route::post("/lineup-submissions/{match}/{team}/reject", [LineupSubmissionController::class, "reject"])->name("lineup-submissions.reject");
    Route::post("/lineup-submissions/{match}/{team}/lock", [LineupSubmissionController::class, "lock"])->name("lineup-submissions.lock");
    Route::get("/lineup-submissions/{match}/{team}/pdf", [LineupSubmissionController::class, "pdf"])->name("lineup-submissions.pdf");

    // Jersey Colour Submissions
    Route::get("/matches/{match}/jerseys/{team}/edit", [JerseySubmissionController::class, "edit"])->name("jerseys.edit");
    Route::post("/matches/{match}/jerseys/{team}", [JerseySubmissionController::class, "store"])->name("jerseys.store");
    Route::post("/matches/{match}/jerseys/{team}/confirm", [JerseySubmissionController::class, "confirm"])->name("jerseys.confirm");
    Route::post("/matches/{match}/jerseys/{team}/request-amendment", [JerseySubmissionController::class, "requestAmendment"])->name("jerseys.request-amendment");

    // Match Day Photos (private JBFA record - Super Admin & League Admin / Match Commissioner only)
    Route::get("/matches/{match}/photos", [MatchDayPhotoController::class, "index"])->name("match-photos.index");
    Route::post("/matches/{match}/photos", [MatchDayPhotoController::class, "upload"])->name("match-photos.upload");
    Route::get("/matches/{match}/photos/{category}/file", [MatchDayPhotoController::class, "file"])->name("match-photos.file");
    Route::delete("/matches/{match}/photos/{category}", [MatchDayPhotoController::class, "destroy"])->name("match-photos.destroy");

    // User Management (Super Admin only)
    // Head Match Commissioner: MC assignment + monitoring dashboard
    Route::get("/mc-assignment", [McAssignmentController::class, "index"])->name("mc-assignment.index");
    Route::post("/mc-assignment/{match}/assign", [McAssignmentController::class, "assign"])->name("mc-assignment.assign");
    Route::get("/mc-assignment/{match}/history", [McAssignmentController::class, "history"])->name("mc-assignment.history");
    Route::get("/head-mc/dashboard", [McAssignmentController::class, "dashboard"])->name("head-mc.dashboard");

    Route::get("/admin/users", [UserManagementController::class, "index"])->name("admin.users.index");
    Route::patch("/admin/users/{user}/role", [UserManagementController::class, "updateRole"])->name("admin.users.update-role");
    Route::patch("/admin/users/{user}/team", [UserManagementController::class, "assignTeam"])->name("admin.users.assign-team");
    Route::post("/admin/users/{user}/verify-email", [UserManagementController::class, "verifyEmail"])->name("admin.users.verify-email");
            Route::patch("/admin/users/{user}/name", [UserManagementController::class, "updateName"])->name("admin.users.update-name");
            Route::patch("/admin/users/{user}/toggle-active", [UserManagementController::class, "toggleActive"])->name("admin.users.toggle-active");
    Route::delete("/admin/users/{user}", [UserManagementController::class, "destroy"])->name("admin.users.destroy");

    // News Management
    Route::get('/admin/news', [NewsController::class, 'adminIndex'])->name('news.admin');
    Route::get('/admin/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/admin/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/admin/news/{id}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/admin/news/{id}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/admin/news/{id}', [NewsController::class, 'destroy'])->name('news.destroy');

    // Promotions
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/offer/{team}', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/offer/{team}', [PromotionController::class, 'store'])->name('promotions.store');
    Route::get('/promotions/{offer}/respond', [PromotionController::class, 'respond'])->name('promotions.respond');
    Route::post('/promotions/{offer}/accept', [PromotionController::class, 'accept'])->name('promotions.accept');
    Route::post('/promotions/{offer}/decline', [PromotionController::class, 'decline'])->name('promotions.decline');
    Route::get('/promotions/{offer}/letter', [PromotionController::class, 'letter'])->name('promotions.letter');
    // Relegations
    Route::get('/relegations/relegate/{team}', [PromotionController::class, 'createRelegation'])->name('relegations.create');
    Route::post('/relegations/relegate/{team}', [PromotionController::class, 'storeRelegation'])->name('relegations.store');
    Route::patch('/teams/{team}/rejection-reason', [\App\Http\Controllers\TeamController::class, 'updateRejectionReason'])->name('teams.update-rejection-reason');
});