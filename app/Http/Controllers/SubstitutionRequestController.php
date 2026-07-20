<?php

namespace App\Http\Controllers;

use App\Models\MatchEvent;
use App\Models\MatchGame;
use App\Models\Player;
use App\Models\SubstitutionRequest;
use App\Services\SubstitutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubstitutionRequestController extends Controller
{
    /** Team Manager submits a substitution request (no manual minute). */
    public function store(Request $request, $matchId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_out_id' => ['required', 'exists:players,id'],
            'player_in_id' => ['required', 'exists:players,id', 'different:player_out_id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $teamId = (int) $validated['team_id'];

        if (!in_array($teamId, [$match->home_team_id, $match->away_team_id])) {
            abort(404);
        }

        // Only the Team Manager of that team (or a match operator) may request.
        $isTm = $user->isTeamManager() && $user->managesTeam($teamId);
        if (!$isTm && !$match->canOperateBy($user)) {
            abort(403);
        }

        if (!in_array($match->status, ['live', 'half_time', 'second_half'])) {
            return back()->with('error', __('app.sub_match_not_live'));
        }

        $svc = app(SubstitutionService::class);

        if (!$svc->onFieldIds($match, $teamId)->contains((int) $validated['player_out_id'])) {
            return back()->with('error', __('app.sub_out_not_on_field'));
        }
        if (!$svc->benchIds($match, $teamId)->contains((int) $validated['player_in_id'])) {
            return back()->with('error', __('app.sub_in_not_available'));
        }

        // Avoid the same player being requested twice while a request is pending.
        $dupe = SubstitutionRequest::where('match_game_id', $match->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($validated) {
                $q->where('player_out_id', $validated['player_out_id'])
                    ->orWhere('player_in_id', $validated['player_in_id']);
            })
            ->exists();
        if ($dupe) {
            return back()->with('error', __('app.sub_already_pending'));
        }

        $playerOut = Player::findOrFail($validated['player_out_id']);
        $playerIn = Player::findOrFail($validated['player_in_id']);

        if ($svc->wouldViolateU23($match, $teamId, $playerOut, $playerIn)) {
            return back()->with('error', __('app.sub_u23_warning'));
        }

        SubstitutionRequest::create([
            'match_game_id' => $match->id,
            'team_id' => $teamId,
            'player_out_id' => $playerOut->id,
            'player_in_id' => $playerIn->id,
            'minute' => $svc->currentMinute($match),
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
            'requested_by' => $user->id,
        ]);

        return back()->with('success', __('app.sub_request_sent'));
    }

    /** Match Commissioner approves: create the substitution event + mark done. */
    public function approve($matchId, $requestId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        if (!$match->canOperateBy($user)) {
            abort(403);
        }

        $req = SubstitutionRequest::where('match_game_id', $match->id)->findOrFail($requestId);
        if (!$req->isPending()) {
            return back()->with('error', __('app.sub_already_reviewed'));
        }

        $svc = app(SubstitutionService::class);

        // State may have shifted since the request; re-validate before applying.
        if (!$svc->onFieldIds($match, $req->team_id)->contains($req->player_out_id)) {
            return back()->with('error', __('app.sub_out_not_on_field'));
        }
        if (!$svc->benchIds($match, $req->team_id)->contains($req->player_in_id)) {
            return back()->with('error', __('app.sub_in_not_available'));
        }
        if ($svc->wouldViolateU23($match, $req->team_id, $req->playerOut, $req->playerIn)) {
            return back()->with('error', __('app.sub_u23_warning'));
        }

        DB::transaction(function () use ($match, $req, $svc, $user) {
            $event = MatchEvent::create([
                'match_game_id' => $match->id,
                'team_id' => $req->team_id,
                'player_id' => $req->player_out_id,
                'related_player_id' => $req->player_in_id,
                'event_type' => 'substitution',
                'minute' => $req->minute ?: $svc->currentMinute($match),
                'notes' => $req->reason,
                'recorded_by_user_id' => $user->id,
            ]);

            $req->update([
                'status' => 'approved',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'match_event_id' => $event->id,
            ]);
        });

        return back()->with('success', __('app.sub_approved'));
    }

    /** Match Commissioner rejects with an optional reason. */
    public function reject(Request $request, $matchId, $requestId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        if (!$match->canOperateBy($user)) {
            abort(403);
        }

        $req = SubstitutionRequest::where('match_game_id', $match->id)->findOrFail($requestId);
        if (!$req->isPending()) {
            return back()->with('error', __('app.sub_already_reviewed'));
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $req->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('success', __('app.sub_rejected'));
    }
}
