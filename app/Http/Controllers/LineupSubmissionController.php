<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\DisciplinaryFine;
use App\Models\LineupSubmission;
use App\Models\MatchGame;
use App\Models\MatchLineup;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LineupSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MatchGame::with(['homeTeam', 'awayTeam', 'competition', 'lineupSubmissions']);

        $teamIds = $user->isTeamManager() ? $user->managedTeamIds() : [];
        if (!empty($teamIds)) {
            $query->where(function ($q) use ($teamIds) {
                $q->whereIn('home_team_id', $teamIds)
                   ->orWhereIn('away_team_id', $teamIds);
            });
        }

        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        $query->whereIn('status', ['scheduled', 'in_progress']);
        $matches = $query->orderBy('match_date')->paginate(20)->withQueryString();
        $competitions = Competition::orderBy('name')->get();

        return view('lineup-submissions.index', compact('matches', 'competitions', 'user'));
    }

    public function edit($matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        if ($user->isTeamManager() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        // The squad belongs to the club and is shared across competitions, so
        // fetch by the club (via the team's club-keyed relationship), not by the
        // per-competition team row.
        $players = $team->players()
            ->where('status', 'active')
            ->orderBy('jersey_number')
            ->get();

        $suspendedPlayerIds = DisciplinaryFine::where('team_id', $team->id)
            ->where('is_suspended', true)
            ->whereNull('suspension_lifted_at')
            ->pluck('player_id')
            ->toArray();

        $submission = LineupSubmission::where('match_game_id', $match->id)
            ->where('team_id', $team->id)
            ->first();

        $selectedStarting = [];
        $selectedSubs = [];

        if ($submission) {
            if (!$submission->canEdit() && !$user->isSuper() && !$user->isLeagueAdmin()) {
                return redirect()->route('lineup-submissions.show', [$match->id, $team->id])
                    ->with('error', __('app.lineup_cannot_edit'));
            }
            $selectedStarting = $submission->lineups()->where('is_starting', true)->pluck('player_id')->toArray();
            $selectedSubs = $submission->lineups()->where('is_starting', false)->pluck('player_id')->toArray();
        }

        return view('lineup-submissions.edit', compact('match', 'team', 'players', 'submission', 'selectedStarting', 'selectedSubs', 'suspendedPlayerIds'));
    }

    public function store(Request $request, $matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        if ($user->isTeamManager() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'starting' => ['required', 'array', 'size:11'],
            'starting.*' => ['required', 'exists:players,id'],
            'substitutes' => ['nullable', 'array', 'max:9'],
            'substitutes.*' => ['exists:players,id'],
        ]);

        $startingIds = $validated['starting'];
        $subIds = $validated['substitutes'] ?? [];

        $overlap = array_intersect($startingIds, $subIds);
        if (!empty($overlap)) {
            return back()->with('error', __('app.lineup_player_duplicate'))->withInput();
        }

        $suspendedPlayerIds = DisciplinaryFine::where('team_id', $team->id)
            ->where('is_suspended', true)
            ->whereNull('suspension_lifted_at')
            ->pluck('player_id')
            ->toArray();

        $allSelected = array_merge($startingIds, $subIds);
        $suspendedSelected = array_intersect($allSelected, $suspendedPlayerIds);
        if (!empty($suspendedSelected)) {
            return back()->with('error', __('app.lineup_suspended_player'))->withInput();
        }

        DB::transaction(function () use ($match, $team, $user, $startingIds, $subIds) {
            $submission = LineupSubmission::updateOrCreate(
                ['match_game_id' => $match->id, 'team_id' => $team->id],
                [
                    'submitted_by' => $user->id,
                    'status' => 'draft',
                    'rejection_reason' => null,
                ]
            );

            $submission->lineups()->delete();
            MatchLineup::where('match_game_id', $match->id)
                ->where('team_id', $team->id)
                ->whereNull('lineup_submission_id')
                ->delete();

            foreach ($startingIds as $playerId) {
                $player = Player::find($playerId);
                MatchLineup::create([
                    'lineup_submission_id' => $submission->id,
                    'match_game_id' => $match->id,
                    'team_id' => $team->id,
                    'player_id' => $playerId,
                    'jersey_number' => $player->jersey_number,
                    'position' => $player->position,
                    'is_starting' => true,
                ]);
            }

            foreach ($subIds as $playerId) {
                $player = Player::find($playerId);
                MatchLineup::create([
                    'lineup_submission_id' => $submission->id,
                    'match_game_id' => $match->id,
                    'team_id' => $team->id,
                    'player_id' => $playerId,
                    'jersey_number' => $player->jersey_number,
                    'position' => $player->position,
                    'is_starting' => false,
                ]);
            }
        });

        return redirect()->route('lineup-submissions.show', [$match->id, $team->id])
            ->with('success', __('app.lineup_saved'));
    }

    public function show($matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        if ($user->isTeamManager() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        $submission = LineupSubmission::where('match_game_id', $match->id)
            ->where('team_id', $team->id)
            ->with(['lineups.player', 'submittedByUser', 'reviewedByUser'])
            ->first();

        if (!$submission) {
            return redirect()->route('lineup-submissions.edit', [$match->id, $team->id]);
        }

        $starting = $submission->lineups->where('is_starting', true)->sortBy('jersey_number');
        $subs = $submission->lineups->where('is_starting', false)->sortBy('jersey_number');

        return view('lineup-submissions.show', compact('match', 'team', 'submission', 'starting', 'subs'));
    }

    public function submit($matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        if ($user->isTeamManager() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        $submission = LineupSubmission::where('match_game_id', $match->id)
            ->where('team_id', $team->id)
            ->firstOrFail();

        if (!$submission->canSubmit()) {
            return back()->with('error', __('app.lineup_cannot_submit'));
        }

        $startingCount = $submission->lineups()->where('is_starting', true)->count();
        if ($startingCount !== 11) {
            return back()->with('error', __('app.lineup_need_11'));
        }

        $submission->update([
            'status' => 'submitted',
            'submitted_by' => $user->id,
            'submitted_at' => now(),
        ]);

        return redirect()->route('lineup-submissions.show', [$match->id, $team->id])
            ->with('success', __('app.lineup_submitted'));
    }

    public function review($matchId)
    {
        $user = Auth::user();
        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($matchId);

        // Admins, the Head MC and the assigned Match Commissioner may review.
        if (!$match->canOperateBy($user)) {
            abort(403);
        }

        $submissions = LineupSubmission::where('match_game_id', $match->id)
            ->with(['team', 'lineups.player', 'submittedByUser'])
            ->get();

        return view('lineup-submissions.review', compact('match', 'submissions'));
    }

    public function approve($matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        if (!$match->canOperateBy($user)) {
            abort(403);
        }

        $submission = LineupSubmission::where('match_game_id', $matchId)
            ->where('team_id', $teamId)
            ->firstOrFail();

        if ($submission->status !== 'submitted') {
            return back()->with('error', __('app.lineup_not_submitted'));
        }

        // Squad is shared at club level, so validate membership against the
        // club, not the per-competition team row.
        $submissionClubId = (int) Team::whereKey($teamId)->value('club_id');
        $players = $submission->lineups()->with('player')->get();
        foreach ($players as $lineup) {
            if ((int) $lineup->player->club_id !== $submissionClubId) {
                return back()->with('error', __('app.lineup_wrong_team', ['player' => $lineup->player->name]));
            }
            if ($lineup->player->status === 'suspended') {
                return back()->with('error', __('app.lineup_suspended', ['player' => $lineup->player->name]));
            }
        }

        $submission->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', __('app.lineup_approved'));
    }

    public function reject(Request $request, $matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        if (!$match->canOperateBy($user)) {
            abort(403);
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $submission = LineupSubmission::where('match_game_id', $matchId)
            ->where('team_id', $teamId)
            ->firstOrFail();

        if ($submission->status !== 'submitted') {
            return back()->with('error', __('app.lineup_not_submitted'));
        }

        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', __('app.lineup_rejected'));
    }

    public function lock($matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        if (!$match->canOperateBy($user)) {
            abort(403);
        }

        $submission = LineupSubmission::where('match_game_id', $matchId)
            ->where('team_id', $teamId)
            ->firstOrFail();

        if ($submission->status !== 'approved') {
            return back()->with('error', __('app.lineup_not_approved'));
        }

        $submission->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);

        return back()->with('success', __('app.lineup_locked'));
    }

    public function pdf($matchId, $teamId)
    {
        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        $submission = LineupSubmission::where('match_game_id', $match->id)
            ->where('team_id', $team->id)
            ->with(['lineups.player', 'submittedByUser', 'reviewedByUser'])
            ->firstOrFail();

        $starting = $submission->lineups->where('is_starting', true)->sortBy('jersey_number');
        $subs = $submission->lineups->where('is_starting', false)->sortBy('jersey_number');

        $opponent = $match->home_team_id === $team->id ? $match->awayTeam : $match->homeTeam;

        return view('lineup-submissions.pdf', compact('match', 'team', 'opponent', 'submission', 'starting', 'subs'));
    }
}
