<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchGame;
use App\Models\Standing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StandingController extends Controller
{
    public function index(Request $request)
    {
        $competitions = Competition::orderBy('name')->get();
        $selectedCompetition = null;
        $standings = collect();

        if ($request->filled('competition_id')) {
            $selectedCompetition = Competition::findOrFail($request->competition_id);
        } else {
            $selectedCompetition = Competition::where('status', 'active')->first();
        }

        if ($selectedCompetition) {
            $standings = Standing::with('team')
                ->where('competition_id', $selectedCompetition->id)
                ->orderBy('position')
                ->get();
        }

        return view('standings.index', compact('competitions', 'selectedCompetition', 'standings'));
    }

    public function recalculate($competitionId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competition = Competition::findOrFail($competitionId);

        Standing::where('competition_id', $competition->id)->delete();

        $teamIds = collect();

        $matches = MatchGame::where('competition_id', $competition->id)
            ->where('status', 'completed')
            ->get();

        foreach ($matches as $match) {
            $teamIds->push($match->home_team_id);
            $teamIds->push($match->away_team_id);
        }

        $teamIds = $teamIds->unique();

        foreach ($teamIds as $teamId) {
            $played = 0;
            $won = 0;
            $drawn = 0;
            $lost = 0;
            $goalsFor = 0;
            $goalsAgainst = 0;

            foreach ($matches as $match) {
                if ($match->home_team_id === $teamId) {
                    $played++;
                    $goalsFor += $match->home_score;
                    $goalsAgainst += $match->away_score;
                    if ($match->home_score > $match->away_score) {
                        $won++;
                    } elseif ($match->home_score === $match->away_score) {
                        $drawn++;
                    } else {
                        $lost++;
                    }
                } elseif ($match->away_team_id === $teamId) {
                    $played++;
                    $goalsFor += $match->away_score;
                    $goalsAgainst += $match->home_score;
                    if ($match->away_score > $match->home_score) {
                        $won++;
                    } elseif ($match->away_score === $match->home_score) {
                        $drawn++;
                    } else {
                        $lost++;
                    }
                }
            }

            Standing::create([
                'competition_id' => $competition->id,
                'team_id' => $teamId,
                'played' => $played,
                'won' => $won,
                'drawn' => $drawn,
                'lost' => $lost,
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'goal_difference' => $goalsFor - $goalsAgainst,
                'points' => ($won * 3) + $drawn,
                'position' => 0,
            ]);
        }

        $standings = Standing::where('competition_id', $competition->id)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        foreach ($standings as $index => $standing) {
            $standing->update(['position' => $index + 1]);
        }

        return redirect()->route('standings.index', ['competition_id' => $competition->id])
            ->with('success', 'Standings recalculated successfully.');
    }
}
