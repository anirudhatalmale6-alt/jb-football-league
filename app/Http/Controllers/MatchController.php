<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchAuditLog;
use App\Models\MatchEvent;
use App\Services\DisciplinarySyncService;
use App\Services\SubstitutionService;
use App\Models\MatchGame;
use App\Models\MatchJersey;
use App\Models\MatchLineup;
use App\Models\MatchSignature;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchGame::with(['homeTeam.players', 'awayTeam.players', 'competition']);

        if ($request->filled('competition_id') || $request->filled('competition')) {
            $query->where('competition_id', $request->input('competition_id', $request->input('competition')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Archived matches stay hidden from the normal listing. A Super Admin
        // can opt in with ?show_archived=1 to review / restore them.
        $viewer = Auth::user();
        $showArchived = $request->boolean('show_archived') && $viewer && $viewer->isSuper();
        if ($showArchived) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        $matches = $query->orderByDesc('match_date')->paginate(15)->withQueryString();
        $competitions = Competition::orderBy('name')->get();
        $archivedCount = MatchGame::whereNotNull('archived_at')->count();

        return view('matches.index', compact('matches', 'competitions', 'showArchived', 'archivedCount'));
    }

    public function create()
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competitions = Competition::orderBy('name')->get();
        $teams = Team::with('competition')->where('status', 'approved')->orderBy('name')->get();

        return view('matches.create', compact('competitions', 'teams'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'matchday' => ['nullable', 'integer', 'min:1'],
            'match_date' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:scheduled,in_progress,completed,postponed'],
            'referee' => ['nullable', 'string', 'max:255'],
            'assistant_referee_1' => ['nullable', 'string', 'max:255'],
            'assistant_referee_2' => ['nullable', 'string', 'max:255'],
            'fourth_official' => ['nullable', 'string', 'max:255'],
            'match_commissioner' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated["status"] = $validated["status"] ?? "scheduled";
        MatchGame::create($validated);

        return redirect()->route('matches.index')
            ->with('success', 'Match created successfully.');
    }

    public function show($id)
    {
        $match = MatchGame::with([
            'homeTeam.players',
            'awayTeam.players',
            'competition',
            'lineups.player',
            'lineups.team',
            'events.player',
            'events.team',
            'events.relatedPlayer',
            'signatures',
            'jerseys',
            'lineupSubmissions',
            'matchDayPhotos',
        ])->findOrFail($id);

        $homePlayers = $match->homeTeam->players ?? collect();
        $awayPlayers = $match->awayTeam->players ?? collect();

        $homeJersey = $match->jerseys->firstWhere('team_id', $match->home_team_id);
        $awayJersey = $match->jerseys->firstWhere('team_id', $match->away_team_id);
        $jerseyClashes = JerseySubmissionController::detectClashes($homeJersey, $awayJersey);

        // Substitution requests + (for a Team Manager) their current on-field
        // and bench players, used by the Request Substitution form.
        $substitutionRequests = $match->substitutionRequests()
            ->with(['team', 'playerOut', 'playerIn', 'requestedBy'])
            ->orderByDesc('id')
            ->get();

        $subService = app(SubstitutionService::class);
        $mySubTeamId = null;
        $subOnField = collect();
        $subBench = collect();
        $viewer = Auth::user();
        if ($viewer && $viewer->isTeamManager()) {
            foreach ([$match->home_team_id, $match->away_team_id] as $tid) {
                if ($tid && $viewer->managesTeam($tid)) {
                    $mySubTeamId = $tid;
                    break;
                }
            }
            if ($mySubTeamId) {
                $subOnField = $subService->onFieldPlayers($match, $mySubTeamId);
                $subBench = $subService->benchPlayers($match, $mySubTeamId);
            }
        }

        return view('matches.show', compact('match', 'homePlayers', 'awayPlayers', 'homeJersey', 'awayJersey', 'jerseyClashes', 'substitutionRequests', 'mySubTeamId', 'subOnField', 'subBench'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);
        $competitions = Competition::orderBy('name')->get();
        $teams = Team::with('competition')->where('status', 'approved')->orderBy('name')->get();

        return view('matches.edit', compact('match', 'competitions', 'teams'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'matchday' => ['nullable', 'integer', 'min:1'],
            'match_date' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:scheduled,in_progress,completed,postponed'],
            'home_score' => ['nullable', 'integer', 'min:0'],
            'away_score' => ['nullable', 'integer', 'min:0'],
            'referee' => ['nullable', 'string', 'max:255'],
            'assistant_referee_1' => ['nullable', 'string', 'max:255'],
            'assistant_referee_2' => ['nullable', 'string', 'max:255'],
            'fourth_official' => ['nullable', 'string', 'max:255'],
            'match_commissioner' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $match->update($validated);

        return redirect()->route('matches.show', $match->id)
            ->with('success', 'Match updated successfully.');
    }

    /**
     * Permanently delete a match. Super Admin only. Matches carrying official
     * data require the admin to type DELETE to confirm. All related records are
     * cleaned up (DB cascades handle most child rows; MC assignment logs and
     * uploaded photo files are removed here) and the action is audit-logged.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isSuper()) {
            abort(403);
        }

        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($id);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Extra protection for official/played matches: require typed DELETE.
        if ($match->isOfficialData() && strtoupper(trim((string) $request->input('confirm_text'))) !== 'DELETE') {
            return redirect()->route('matches.index')
                ->with('error', __('app.match_delete_confirm_required'));
        }

        $reason = $this->deletionReason($request);

        DB::transaction(function () use ($match, $reason, $user) {
            // Snapshot to the audit trail BEFORE the row disappears.
            MatchAuditLog::record($match, 'deleted', $reason, $user);

            // Remove uploaded match-day photo files from disk (DB rows cascade,
            // but the physical files would otherwise be orphaned).
            Storage::disk('local')->deleteDirectory('match-day-photos/' . $match->id);

            // MC assignment logs reference the match without a DB cascade.
            DB::table('mc_assignment_logs')->where('match_game_id', $match->id)->delete();

            // Deleting the match cascades events, lineups, jerseys, signatures,
            // photos, lineup submissions and substitution requests; disciplinary
            // fines and knockout slots are preserved (their match link is nulled).
            $match->delete();
        });

        return redirect()->route('matches.index')
            ->with('success', __('app.match_deleted_success'));
    }

    /**
     * Archive a match: hide it from normal listings while keeping every related
     * record intact. Super Admin only. The safer alternative to deletion for
     * official matches.
     */
    public function archive(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isSuper()) {
            abort(403);
        }

        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($id);

        if ($match->isArchived()) {
            return redirect()->route('matches.index')
                ->with('error', __('app.match_already_archived'));
        }

        $reason = $this->deletionReason($request);

        DB::transaction(function () use ($match, $reason, $user) {
            $match->update([
                'archived_at' => now(),
                'archived_by_user_id' => $user->id,
                'archive_reason' => $reason,
            ]);
            MatchAuditLog::record($match, 'archived', $reason, $user);
        });

        return redirect()->route('matches.index')
            ->with('success', __('app.match_archived_success'));
    }

    /** Restore an archived match back into the normal listing. Super Admin only. */
    public function restore(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isSuper()) {
            abort(403);
        }

        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($id);

        DB::transaction(function () use ($match, $user) {
            $match->update([
                'archived_at' => null,
                'archived_by_user_id' => null,
                'archive_reason' => null,
            ]);
            MatchAuditLog::record($match, 'restored', null, $user);
        });

        return redirect()->route('matches.index', ['show_archived' => 1])
            ->with('success', __('app.match_restored_success'));
    }

    /** Audit trail of every archive / delete / restore action. Super Admin only. */
    public function auditLog()
    {
        if (!Auth::user()->isSuper()) {
            abort(403);
        }

        $logs = MatchAuditLog::with('performedBy')->orderByDesc('id')->paginate(30);

        return view('matches.audit-log', compact('logs'));
    }

    /**
     * Build a human-readable deletion/archive reason from the reason preset and
     * the optional free-text note.
     */
    private function deletionReason(Request $request): ?string
    {
        $preset = trim((string) $request->input('reason'));
        $note = trim((string) $request->input('reason_note'));

        if ($preset === 'Other' && $note !== '') {
            return $note;
        }
        if ($preset !== '' && $note !== '') {
            return $preset . ' - ' . $note;
        }
        return $preset !== '' ? $preset : ($note !== '' ? $note : null);
    }

    public function lineup($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::with(['homeTeam.players', 'awayTeam.players', 'lineups.player'])->findOrFail($id);
        $homePlayers = $match->homeTeam->players ?? collect();
        $awayPlayers = $match->awayTeam->players ?? collect();
        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);

        return view('matches.lineup', compact('match', 'homePlayers', 'awayPlayers', 'homeLineup', 'awayLineup'));
    }

    public function storeLineup(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['required', 'exists:players,id'],
            'jersey_number' => ['required', 'integer', 'min:1', 'max:99'],
            'position' => ['required', 'string', 'max:50'],
        ]);

        $existing = MatchLineup::where('match_game_id', $match->id)
            ->where('player_id', $validated['player_id'])
            ->first();

        if ($existing) {
            return redirect()->route('matches.lineup', $match->id)
                ->with('error', 'Player is already in the lineup.');
        }

        MatchLineup::create([
            'match_game_id' => $match->id,
            'team_id' => $validated['team_id'],
            'player_id' => $validated['player_id'],
            'jersey_number' => $validated['jersey_number'],
            'position' => $validated['position'],
            'is_starting' => $request->boolean('is_starting'),
        ]);

        return redirect()->route('matches.lineup', $match->id)
            ->with('success', 'Player added to lineup.');
    }

    public function deleteLineup($matchId, $lineupId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($matchId);
        $lineup = MatchLineup::where('match_game_id', $match->id)->findOrFail($lineupId);
        $lineup->delete();

        return redirect()->route('matches.lineup', $match->id)
            ->with('success', 'Player removed from lineup.');
    }

    public function events($id)
    {
        $match = MatchGame::with([
            'homeTeam.players',
            'awayTeam.players',
            'events.player',
            'events.team',
            'events.relatedPlayer',
        ])->findOrFail($id);

        // Admins, the Head MC and the assigned Match Commissioner may all open
        // the Match Events page for this match.
        if (!$match->canOperateBy(Auth::user())) {
            abort(403);
        }
        $homePlayers = $match->homeTeam->players ?? collect();
        $awayPlayers = $match->awayTeam->players ?? collect();
        $events = $match->events->sortBy('minute');

        return view('matches.events', compact('match', 'homePlayers', 'awayPlayers', 'events'));
    }

    public function storeEvent(Request $request, $id)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($id);

        // Only an operator assigned to (or supervising) this match may add events.
        if (!$match->canOperateBy($user)) {
            abort(403);
        }
        if (!$match->canEditBy($user)) {
            return back()->with('error', __('app.match_locked_no_edit'));
        }

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['required', 'exists:players,id'],
            'event_type' => ['required', 'in:goal,own_goal,yellow_card,red_card,substitution,substitution_in,substitution_out,penalty_scored,penalty_missed,injury,var_review,other'],
            'minute' => ['required', 'integer', 'min:1', 'max:120'],
            'extra_time_minute' => ['nullable', 'integer', 'min:1'],
            'related_player_id' => ['nullable', 'exists:players,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['match_game_id'] = $match->id;
        $validated['recorded_by_user_id'] = $user->id;

        MatchEvent::create($validated);

        $this->recalculateScore($match);

        // Auto-generate/refresh disciplinary fines for card events.
        if (in_array($validated['event_type'], ['yellow_card', 'red_card'])) {
            app(DisciplinarySyncService::class)
                ->syncPlayerCompetition((int) $validated['player_id'], (int) $match->competition_id);
        }

        // Stay on whichever page the operator was on (Match Details Quick Event
        // panel or the Match Events page) instead of forcing a redirect.
        return back()->with('success', 'Event added successfully.');
    }

    public function deleteEvent($matchId, $eventId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);

        if (!$match->canOperateBy($user)) {
            abort(403);
        }
        if (!$match->canEditBy($user)) {
            return back()->with('error', __('app.match_locked_no_edit'));
        }
        $event = MatchEvent::where('match_game_id', $match->id)->findOrFail($eventId);
        $cardPlayerId = $event->player_id;
        $cardEventType = $event->event_type;
        $event->delete();

        $this->recalculateScore($match);

        // Refresh disciplinary fines when a card is removed.
        if (in_array($cardEventType, ['yellow_card', 'red_card']) && $cardPlayerId) {
            app(DisciplinarySyncService::class)
                ->syncPlayerCompetition((int) $cardPlayerId, (int) $match->competition_id);
        }

        return back()->with('success', 'Event removed successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canOperateMatches()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);
        if (!$match->canEditBy($user)) {
            return back()->with("error", __('app.match_locked_no_edit'));
        }
        $newStatus = $request->input("status");
        $allowed = ["scheduled","live","half_time","second_half","full_time","closed"];

        if (!in_array($newStatus, $allowed)) {
            return back()->with("error", "Invalid status.");
        }

        $data = ["status" => $newStatus];

        // Kick-off: always start the clock fresh at 00' and clear any later
        // timestamps left over from a previous run of this match.
        if ($newStatus === "live") {
            $data["live_started_at"] = now();
            $data["half_time_at"] = null;
            $data["second_half_at"] = null;
            $data["full_time_at"] = null;
        }
        // Reset to Scheduled: wipe the whole clock so a re-run starts clean.
        if ($newStatus === "scheduled") {
            $data["live_started_at"] = null;
            $data["half_time_at"] = null;
            $data["second_half_at"] = null;
            $data["full_time_at"] = null;
            $data["closed_at"] = null;
        }
        if ($newStatus === "half_time") {
            $data["half_time_at"] = now();
        }
        if ($newStatus === "second_half") {
            $data["second_half_at"] = now();
        }
        if ($newStatus === "full_time") {
            $data["full_time_at"] = now();
        }
        if ($newStatus === "closed") {
            $match->load("signatures");
            if (!$match->allSignaturesConfirmed()) {
                return back()->with("error", "All 4 e-signatures must be confirmed before closing the match.");
            }
            $data["closed_at"] = now();
            $this->updateStanding($match->competition_id, $match->home_team_id);
            $this->updateStanding($match->competition_id, $match->away_team_id);
        }

        $match->update($data);

        $labels = ["live"=>"Match is LIVE!","half_time"=>"Half Time","second_half"=>"Second Half started","full_time"=>"Full Time","closed"=>"Match Closed","scheduled"=>"Match reset to Scheduled"];
        return back()->with("success", $labels[$newStatus] ?? "Status updated.");
    }

    public function updateScore(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canOperateMatches()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);
        if (!$match->canEditBy($user)) {
            return back()->with("error", __('app.match_locked_no_edit'));
        }
        $validated = $request->validate([
            "home_score" => ["required","integer","min:0"],
            "away_score" => ["required","integer","min:0"],
        ]);
        $match->update($validated);

        return back()->with("success", "Score updated.");
    }

    public function complete($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if ($match->home_score === null || $match->away_score === null) {
            return back()->with('error', 'Please set the match scores before completing.');
        }

        DB::transaction(function () use ($match) {
            $match->update(['status' => 'completed']);

            $this->updateStanding($match->competition_id, $match->home_team_id);
            $this->updateStanding($match->competition_id, $match->away_team_id);
        });

        return redirect()->route('matches.show', $match->id)
            ->with('success', 'Match marked as completed and standings updated.');
    }


    public function storeSignature(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canOperateMatches()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if (!$match->canEditBy($user)) {
            return back()->with("error", __('app.match_locked_no_edit'));
        }

        $validated = $request->validate([
            "role" => ["required", "in:head_referee,home_team_rep,away_team_rep,match_commissioner"],
            "name" => ["required", "string", "max:255"],
            "signature_data" => ["nullable", "string"],
            "remarks" => ["nullable", "string", "max:2000"],
            "confirmed" => ["required", "accepted"],
        ]);

        MatchSignature::updateOrCreate(
            ["match_game_id" => $match->id, "role" => $validated["role"]],
            [
                "name" => $validated["name"],
                "signature_data" => $validated["signature_data"] ?? null,
                "remarks" => $validated["remarks"] ?? null,
                "confirmed" => true,
                "signed_at" => now(),
                "signed_by_user_id" => Auth::id(),
            ]
        );

        return back()->with("success", MatchSignature::roleLabel($validated["role"]) . " signature confirmed.");
    }

    public function deleteSignature(Request $request, $id, $signatureId)
    {
        $user = Auth::user();
        if (!$user->canOperateMatches()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if (!$match->canEditBy($user)) {
            return back()->with("error", __('app.match_locked_no_edit'));
        }

        $signature = MatchSignature::where("match_game_id", $match->id)->findOrFail($signatureId);
        $roleName = MatchSignature::roleLabel($signature->role);
        $signature->delete();

        return back()->with("success", $roleName . " signature removed.");
    }

    public function storeRemarks(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canOperateMatches()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if (!$match->canEditBy($user)) {
            return back()->with("error", __('app.match_locked_no_edit'));
        }

        $validated = $request->validate([
            "match_remarks" => ["nullable", "string", "max:5000"],
        ]);

        $match->update(["match_remarks" => $validated["match_remarks"]]);

        return back()->with("success", "Match remarks saved.");
    }

    /**
     * MC's final action: submit and lock the match report. Allowed at any
     * minute (early finish / abandoned / walkover) as long as all four
     * e-signatures are confirmed. After this only Super Admin / Head MC can edit.
     */
    public function submitFinalReport(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canOperateMatches()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if ($match->isLocked()) {
            return back()->with("error", __('app.match_already_submitted'));
        }

        $validated = $request->validate([
            "final_minute" => ["nullable", "integer", "min:0", "max:200"],
            "match_remarks" => ["nullable", "string", "max:5000"],
        ]);

        $match->load("signatures");
        if (!$match->allSignaturesConfirmed()) {
            return back()->with("error", __('app.submit_need_signatures'));
        }

        $data = [
            "status" => "closed",
            "closed_at" => now(),
            "final_submitted_at" => now(),
            "final_submitted_by_user_id" => $user->id,
            "final_minute" => $validated["final_minute"] ?? null,
        ];
        if (!empty($validated["match_remarks"])) {
            $data["match_remarks"] = $validated["match_remarks"];
        }

        $match->update($data);

        $this->updateStanding($match->competition_id, $match->home_team_id);
        $this->updateStanding($match->competition_id, $match->away_team_id);

        return back()->with("success", __('app.final_report_submitted'));
    }

    public function unlockMatch($id)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isHeadMatchCommissioner()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if (!$match->isLocked()) {
            return back()->with("error", "Only a submitted/closed match can be unlocked.");
        }

        $match->update([
            "status" => "full_time",
            "closed_at" => null,
            "final_submitted_at" => null,
            "final_submitted_by_user_id" => null,
        ]);

        return back()->with("success", "Match unlocked. You can now edit the report.");
    }

    private function recalculateScore(MatchGame $match): void
    {
        $homeScore = 0;
        $awayScore = 0;
        $events = MatchEvent::where("match_game_id", $match->id)->get();
        foreach ($events as $event) {
            if (in_array($event->event_type, ["goal", "penalty_scored"])) {
                if ($event->team_id === $match->home_team_id) {
                    $homeScore++;
                } else {
                    $awayScore++;
                }
            } elseif ($event->event_type === "own_goal") {
                if ($event->team_id === $match->home_team_id) {
                    $awayScore++;
                } else {
                    $homeScore++;
                }
            }
        }
        $match->update(["home_score" => $homeScore, "away_score" => $awayScore]);
    }

    private function updateStanding(int $competitionId, int $teamId): void
    {
        $matches = MatchGame::where('competition_id', $competitionId)
            ->whereIn('status', ['completed', 'closed'])
            ->where(function ($q) use ($teamId) {
                $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId);
            })
            ->get();

        $played = $matches->count();
        $won = 0;
        $drawn = 0;
        $lost = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($matches as $m) {
            if ($m->home_team_id === $teamId) {
                $goalsFor += $m->home_score;
                $goalsAgainst += $m->away_score;
                if ($m->home_score > $m->away_score) {
                    $won++;
                } elseif ($m->home_score === $m->away_score) {
                    $drawn++;
                } else {
                    $lost++;
                }
            } else {
                $goalsFor += $m->away_score;
                $goalsAgainst += $m->home_score;
                if ($m->away_score > $m->home_score) {
                    $won++;
                } elseif ($m->away_score === $m->home_score) {
                    $drawn++;
                } else {
                    $lost++;
                }
            }
        }

        Standing::updateOrCreate(
            ['competition_id' => $competitionId, 'team_id' => $teamId],
            [
                'played' => $played,
                'won' => $won,
                'drawn' => $drawn,
                'lost' => $lost,
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'goal_difference' => $goalsFor - $goalsAgainst,
                'points' => ($won * 3) + $drawn,
            ]
        );

        $standings = Standing::where('competition_id', $competitionId)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        foreach ($standings as $index => $standing) {
            $standing->update(['position' => $index + 1]);
        }
    }
}
