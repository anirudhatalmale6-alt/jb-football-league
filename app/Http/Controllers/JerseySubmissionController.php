<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\MatchJersey;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JerseySubmissionController extends Controller
{
    /** Distance below this (0-441 RGB scale) is flagged as a possible clash. */
    private const CLASH_THRESHOLD = 70;

    /**
     * Team manager (own team) or admin: show/edit the jersey submission form.
     */
    public function edit($matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])->findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        $this->authorizeTeam($user, $team, $match);

        $jersey = MatchJersey::firstOrNew([
            'match_game_id' => $match->id,
            'team_id' => $team->id,
        ]);

        if ($jersey->exists && !$jersey->canEdit() && !$user->isSuper() && !$user->isLeagueAdmin()) {
            return redirect()->route('matches.show', $match->id)
                ->with('error', 'This jersey submission is locked and can no longer be edited.');
        }

        return view('jerseys.edit', compact('match', 'team', 'jersey'));
    }

    /**
     * Save the jersey submission (draft or submit).
     */
    public function store(Request $request, $matchId, $teamId)
    {
        $user = Auth::user();
        $match = MatchGame::findOrFail($matchId);
        $team = Team::findOrFail($teamId);

        $this->authorizeTeam($user, $team, $match);

        // Team Managers only pick colours now — the colour name is derived
        // automatically from the chosen hex (no typed name field).
        $validated = $request->validate([
            'kit_type' => ['required', 'in:primary,alternative'],
            'shirt_hex' => ['required', 'string', 'max:7'],
            'shorts_hex' => ['required', 'string', 'max:7'],
            'socks_hex' => ['required', 'string', 'max:7'],
            'gk_shirt_hex' => ['required', 'string', 'max:7'],
            'gk_shorts_hex' => ['required', 'string', 'max:7'],
            'gk_socks_hex' => ['required', 'string', 'max:7'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'action' => ['required', 'in:draft,submit'],
        ]);

        $jersey = MatchJersey::firstOrNew([
            'match_game_id' => $match->id,
            'team_id' => $team->id,
        ]);

        if ($jersey->exists && !$jersey->canEdit() && !$user->isSuper() && !$user->isLeagueAdmin()) {
            return redirect()->route('matches.show', $match->id)
                ->with('error', 'This jersey submission is locked and can no longer be edited.');
        }

        $jersey->fill([
            'kit_type' => $validated['kit_type'],
            'shirt_hex' => $validated['shirt_hex'],
            'shirt_name' => MatchJersey::colourName($validated['shirt_hex']),
            'shorts_hex' => $validated['shorts_hex'],
            'shorts_name' => MatchJersey::colourName($validated['shorts_hex']),
            'socks_hex' => $validated['socks_hex'],
            'socks_name' => MatchJersey::colourName($validated['socks_hex']),
            'gk_shirt_hex' => $validated['gk_shirt_hex'],
            'gk_shirt_name' => MatchJersey::colourName($validated['gk_shirt_hex']),
            'gk_shorts_hex' => $validated['gk_shorts_hex'],
            'gk_shorts_name' => MatchJersey::colourName($validated['gk_shorts_hex']),
            'gk_socks_hex' => $validated['gk_socks_hex'],
            'gk_socks_name' => MatchJersey::colourName($validated['gk_socks_hex']),
        ]);

        if ($request->hasFile('photo')) {
            $jersey->photo = $request->file('photo')->store('jerseys', 'public');
        }

        if ($validated['action'] === 'submit') {
            $jersey->status = 'submitted';
            $jersey->submitted_by = $user->id;
            $jersey->submitted_at = now();
        } elseif (!$jersey->exists) {
            $jersey->status = 'draft';
        }

        $jersey->save();

        $msg = $validated['action'] === 'submit'
            ? 'Jersey colours submitted successfully.'
            : 'Jersey colours saved as draft.';

        return redirect()->route('matches.show', $match->id)->with('success', $msg);
    }

    /**
     * Match Commissioner / League Admin: confirm the jersey arrangement.
     */
    public function confirm($matchId, $teamId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($matchId);
        $jersey = MatchJersey::where('match_game_id', $match->id)
            ->where('team_id', $teamId)
            ->firstOrFail();

        if ($jersey->status === 'draft') {
            return back()->with('error', 'The team has not submitted their jersey colours yet.');
        }

        $jersey->status = 'confirmed';
        $jersey->confirmed_by = $user->id;
        $jersey->confirmed_at = now();
        $jersey->save();

        return back()->with('success', 'Jersey colours confirmed.');
    }

    /**
     * Match Commissioner / League Admin: request an amendment.
     */
    public function requestAmendment(Request $request, $matchId, $teamId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'amendment_note' => ['required', 'string', 'max:1000'],
        ]);

        $match = MatchGame::findOrFail($matchId);
        $jersey = MatchJersey::where('match_game_id', $match->id)
            ->where('team_id', $teamId)
            ->firstOrFail();

        $jersey->status = 'amendment_requested';
        $jersey->amendment_note = $validated['amendment_note'];
        $jersey->confirmed_by = null;
        $jersey->confirmed_at = null;
        $jersey->save();

        return back()->with('success', 'Amendment requested. The team manager can now update their submission.');
    }

    private function authorizeTeam($user, Team $team, MatchGame $match): void
    {
        // Team must actually be in this match.
        if (!in_array($team->id, [$match->home_team_id, $match->away_team_id])) {
            abort(404);
        }

        if ($user->isSuper() || $user->isLeagueAdmin()) {
            return;
        }

        if ($user->isTeamManager() && $user->managesTeam($team->id)) {
            return;
        }

        abort(403);
    }

    /**
     * Build clash warnings between the two teams' jerseys.
     * Returns an array of human-readable warning strings.
     */
    public static function detectClashes(?MatchJersey $home, ?MatchJersey $away): array
    {
        $warnings = [];
        if (!$home || !$away) {
            return $warnings;
        }

        $checks = [
            ['Home shirt', $home->shirt_hex, 'Away shirt', $away->shirt_hex],
            ['Home goalkeeper shirt', $home->gk_shirt_hex, 'Away goalkeeper shirt', $away->gk_shirt_hex],
            ['Home goalkeeper shirt', $home->gk_shirt_hex, 'Away shirt', $away->shirt_hex],
            ['Away goalkeeper shirt', $away->gk_shirt_hex, 'Home shirt', $home->shirt_hex],
        ];

        foreach ($checks as [$labelA, $hexA, $labelB, $hexB]) {
            $distance = MatchJersey::colourDistance($hexA, $hexB);
            if ($distance !== null && $distance < self::CLASH_THRESHOLD) {
                $warnings[] = "$labelA and $labelB colours may be too similar.";
            }
        }

        return $warnings;
    }
}
