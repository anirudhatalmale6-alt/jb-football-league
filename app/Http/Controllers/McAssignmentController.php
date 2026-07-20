<?php

namespace App\Http\Controllers;

use App\Mail\McAssignedMail;
use App\Models\MatchDayPhoto;
use App\Models\MatchGame;
use App\Models\McAssignmentLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Head Match Commissioner tools: assign / reassign Match Commissioners to
 * matches, and monitor their work through a dashboard. Super Admin and Head
 * Match Commissioner only.
 */
class McAssignmentController extends Controller
{
    private function authorizeHeadMc(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuper() && !$user->isHeadMatchCommissioner())) {
            abort(403);
        }
    }

    /**
     * Accounts that can be appointed to a match. Includes Head Match
     * Commissioners as well, so a Head MC can appoint himself (or another Head
     * MC) to take charge of a match personally.
     */
    private function commissioners()
    {
        return User::whereIn('role', ['match_commissioner', 'head_match_commissioner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** MC Assignment page: every not-yet-closed match with an assign dropdown. */
    public function index(Request $request)
    {
        $this->authorizeHeadMc();

        $query = MatchGame::with(['homeTeam', 'awayTeam', 'competition', 'assignedMc'])
            ->where('status', '!=', 'closed');

        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }
        if ($request->input('assigned') === 'no') {
            $query->whereNull('assigned_mc_user_id');
        } elseif ($request->input('assigned') === 'yes') {
            $query->whereNotNull('assigned_mc_user_id');
        }

        $matches = $query->orderBy('match_date')->paginate(30)->withQueryString();
        $commissioners = $this->commissioners();
        $competitions = \App\Models\Competition::orderBy('name')->get();
        $viewer = Auth::user();

        return view('mc-assignment.index', compact('matches', 'commissioners', 'competitions', 'viewer'));
    }

    /** Assign or reassign (or clear) the Match Commissioner for one match. */
    public function assign(Request $request, $id)
    {
        $this->authorizeHeadMc();

        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'mc_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newId = $validated['mc_user_id'] ?? null;
        if ($newId) {
            $mc = User::find($newId);
            // A Head MC may be appointed too (he can take charge of a match himself).
            $appointable = $mc && $mc->is_active && ($mc->isMatchCommissioner() || $mc->isHeadMatchCommissioner());
            if (!$appointable) {
                return back()->with('error', __('app.mc_invalid'));
            }
        }

        $prevId = $match->assigned_mc_user_id;
        if ((int) $prevId === (int) $newId) {
            return back()->with('error', __('app.mc_no_change'));
        }

        $match->update(['assigned_mc_user_id' => $newId]);

        McAssignmentLog::create([
            'match_game_id' => $match->id,
            'previous_mc_user_id' => $prevId ?: null,
            'new_mc_user_id' => $newId,
            'changed_by_user_id' => Auth::id(),
            'reason' => $validated['reason'] ?? null,
        ]);

        // Notify the newly assigned Match Commissioner by email (best effort).
        if ($newId) {
            $mc = User::find($newId);
            if ($mc && $mc->email) {
                try {
                    Mail::to($mc->email)->send(new McAssignedMail($match->fresh(['homeTeam', 'awayTeam', 'competition']), $mc));
                } catch (\Throwable $e) {
                    // never block the assignment on a mail failure
                }
            }
        }

        return back()->with('success', $newId ? __('app.mc_assigned_ok') : __('app.mc_unassigned_ok'));
    }

    /** View the full assignment history for a match. */
    public function history($id)
    {
        $this->authorizeHeadMc();
        $match = MatchGame::with(['homeTeam', 'awayTeam', 'assignmentLogs.previousMc', 'assignmentLogs.newMc', 'assignmentLogs.changedBy'])
            ->findOrFail($id);
        return view('mc-assignment.history', compact('match'));
    }

    /** Head MC monitoring dashboard for a chosen day (default today). */
    public function dashboard(Request $request)
    {
        $this->authorizeHeadMc();

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : Carbon::now('Asia/Kuala_Lumpur')->toDateString();

        $matches = MatchGame::with([
            'homeTeam', 'awayTeam', 'competition', 'assignedMc',
            'lineupSubmissions', 'jerseys', 'matchDayPhotos', 'events',
        ])->whereDate('match_date', $date)->orderBy('match_date')->get();

        $rows = $matches->map(fn ($m) => $this->checklist($m));

        $summary = [
            'total' => $rows->count(),
            'assigned' => $rows->where('assigned', true)->count(),
            'unassigned' => $rows->where('assigned', false)->count(),
            'live' => $rows->where('live', true)->count(),
            'completed' => $rows->where('locked', true)->count(),
            'missing_photos' => $rows->where('photos_complete', false)->count(),
            'missing_report' => $rows->where('report_done', false)->count(),
            'incomplete_events' => $rows->where('events_incomplete', true)->count(),
            'pending_confirmation' => $rows->where('pending_confirmation', true)->count(),
        ];

        return view('head-mc.dashboard', compact('rows', 'summary', 'date'));
    }

    /** Build the per-match task-completion checklist used by the dashboard. */
    private function checklist(MatchGame $m): array
    {
        $homeSub = $m->lineupSubmissions->firstWhere('team_id', $m->home_team_id);
        $awaySub = $m->lineupSubmissions->firstWhere('team_id', $m->away_team_id);
        $lineupOk = fn ($s) => $s && in_array($s->status, ['approved', 'locked']);

        $homeJersey = $m->jerseys->firstWhere('team_id', $m->home_team_id);
        $awayJersey = $m->jerseys->firstWhere('team_id', $m->away_team_id);
        $jerseyOk = $homeJersey && $awayJersey
            && $homeJersey->status === 'confirmed' && $awayJersey->status === 'confirmed';

        $photos = $m->matchDayPhotos->pluck('category')->unique()->count();
        $photosTotal = count(MatchDayPhoto::CATEGORIES);

        $eventsCount = $m->events->count();
        $isFinished = $m->isFinished();
        $isLocked = $m->isLocked();

        return [
            'match' => $m,
            'assigned' => $m->assigned_mc_user_id !== null,
            'mc_name' => optional($m->assignedMc)->name,
            'status' => $m->status,
            'live' => $m->isLive() || $m->status === 'half_time',
            'lineup_ok' => $lineupOk($homeSub) && $lineupOk($awaySub),
            'jersey_ok' => $jerseyOk,
            'events_count' => $eventsCount,
            // "incomplete events" = a match that is being/has been played but has no events recorded
            'events_incomplete' => ($m->isLive() || $isFinished) && $eventsCount === 0,
            'photos' => $photos,
            'photos_total' => $photosTotal,
            'photos_complete' => $photos >= $photosTotal,
            'report_done' => $isLocked,
            'locked' => $isLocked,
            // finished play but not yet submitted/locked -> waiting for the MC to confirm
            'pending_confirmation' => $isFinished && !$isLocked,
        ];
    }
}
