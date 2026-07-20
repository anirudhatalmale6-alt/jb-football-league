<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\KnockoutMatch;
use App\Models\MatchGame;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KnockoutController extends Controller
{
    public function bracket($competitionId)
    {
        $competition = Competition::with(['teams' => function ($q) {
            $q->where('status', 'approved')->orderBy('name');
        }])->findOrFail($competitionId);

        $knockoutMatches = KnockoutMatch::where('competition_id', $competitionId)
            ->with(['homeTeam', 'awayTeam', 'matchGame', 'winnerTeam'])
            ->orderBy('round')
            ->orderBy('position')
            ->get();

        $rounds = ['round_of_16', 'quarter_final', 'semi_final', 'final'];
        $bracket = [];
        foreach ($rounds as $round) {
            $bracket[$round] = $knockoutMatches->where('round', $round)->sortBy('position')->values();
        }

        $champion = null;
        $finalMatch = $bracket['final']->first();
        if ($finalMatch && $finalMatch->winner_team_id) {
            $champion = $finalMatch->winnerTeam;
        }

        $bracketInitialized = $knockoutMatches->isNotEmpty();

        return view('competitions.knockout', compact(
            'competition', 'bracket', 'rounds', 'champion', 'bracketInitialized'
        ));
    }

    public function initBracket(Request $request, $competitionId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competition = Competition::findOrFail($competitionId);

        $existing = KnockoutMatch::where('competition_id', $competitionId)->count();
        if ($existing > 0) {
            return back()->with('error', 'Bracket already initialized. Delete it first to reinitialize.');
        }

        DB::transaction(function () use ($competitionId) {
            // R16: 8 matches
            for ($i = 1; $i <= 8; $i++) {
                KnockoutMatch::create([
                    'competition_id' => $competitionId,
                    'round' => 'round_of_16',
                    'position' => $i,
                ]);
            }
            // QF: 4 matches
            for ($i = 1; $i <= 4; $i++) {
                KnockoutMatch::create([
                    'competition_id' => $competitionId,
                    'round' => 'quarter_final',
                    'position' => $i,
                ]);
            }
            // SF: 2 matches
            for ($i = 1; $i <= 2; $i++) {
                KnockoutMatch::create([
                    'competition_id' => $competitionId,
                    'round' => 'semi_final',
                    'position' => $i,
                ]);
            }
            // Final: 1 match
            KnockoutMatch::create([
                'competition_id' => $competitionId,
                'round' => 'final',
                'position' => 1,
            ]);
        });

        return back()->with('success', 'Knockout bracket initialized with 16 slots (R16 → QF → SF → Final).');
    }

    public function seedTeam(Request $request, $competitionId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'knockout_match_id' => 'required|exists:knockout_matches,id',
            'side' => 'required|in:home,away',
            'team_id' => 'required|exists:teams,id',
        ]);

        $km = KnockoutMatch::where('competition_id', $competitionId)->findOrFail($validated['knockout_match_id']);

        if ($km->winner_team_id) {
            return back()->with('error', 'Cannot modify a completed match slot.');
        }

        $field = $validated['side'] === 'home' ? 'home_team_id' : 'away_team_id';
        $km->update([$field => $validated['team_id']]);

        return back()->with('success', 'Team seeded into ' . KnockoutMatch::roundLabel($km->round) . ' Match ' . $km->position . '.');
    }

    public function setWinner(Request $request, $competitionId, $knockoutMatchId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $km = KnockoutMatch::where('competition_id', $competitionId)->findOrFail($knockoutMatchId);

        $validated = $request->validate([
            'winner_team_id' => 'required|exists:teams,id',
            'home_penalty_score' => 'nullable|integer|min:0',
            'away_penalty_score' => 'nullable|integer|min:0',
        ]);

        if ($validated['winner_team_id'] != $km->home_team_id && $validated['winner_team_id'] != $km->away_team_id) {
            return back()->with('error', 'Winner must be one of the two teams in this match.');
        }

        DB::transaction(function () use ($km, $validated, $competitionId) {
            $km->update([
                'winner_team_id' => $validated['winner_team_id'],
                'home_penalty_score' => $validated['home_penalty_score'] ?? null,
                'away_penalty_score' => $validated['away_penalty_score'] ?? null,
            ]);

            // Auto-advance winner to next round
            $nextRound = KnockoutMatch::nextRound($km->round);
            if ($nextRound) {
                $nextPos = KnockoutMatch::nextPosition($km->position);
                $isHome = KnockoutMatch::isHomeInNext($km->position);

                $nextKm = KnockoutMatch::where('competition_id', $competitionId)
                    ->where('round', $nextRound)
                    ->where('position', $nextPos)
                    ->first();

                if ($nextKm) {
                    $field = $isHome ? 'home_team_id' : 'away_team_id';
                    $nextKm->update([$field => $validated['winner_team_id']]);
                }
            }
        });

        $label = KnockoutMatch::roundLabel($km->round);
        $winner = Team::find($validated['winner_team_id']);
        return back()->with('success', $winner->name . ' advances from ' . $label . ' Match ' . $km->position . '!');
    }

    public function resetBracket(Request $request, $competitionId)
    {
        if (!Auth::user()->isSuper()) {
            abort(403);
        }

        KnockoutMatch::where('competition_id', $competitionId)->delete();

        return back()->with('success', 'Knockout bracket has been reset.');
    }

    public function linkMatch(Request $request, $competitionId, $knockoutMatchId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $km = KnockoutMatch::where('competition_id', $competitionId)->findOrFail($knockoutMatchId);

        $validated = $request->validate([
            'match_game_id' => 'nullable|exists:match_games,id',
        ]);

        $km->update(['match_game_id' => $validated['match_game_id']]);

        return back()->with('success', 'Match linked to bracket.');
    }
}
