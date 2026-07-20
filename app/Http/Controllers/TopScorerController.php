<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopScorerController extends Controller
{
    public function index(Request $request)
    {
        $competitions = Competition::orderBy('name')->get();
        $selectedCompetition = $request->get('competition_id');

        $query = MatchEvent::select(
                'match_events.player_id',
                'match_events.team_id',
                DB::raw('COUNT(*) as total_goals'),
                DB::raw('SUM(CASE WHEN match_events.event_type = "penalty_scored" THEN 1 ELSE 0 END) as penalty_goals'),
                DB::raw('COUNT(DISTINCT match_events.match_game_id) as matches_played')
            )
            ->join('match_games', 'match_events.match_game_id', '=', 'match_games.id')
            ->whereIn('match_events.event_type', ['goal', 'penalty_scored'])
            ->groupBy('match_events.player_id', 'match_events.team_id');

        if ($selectedCompetition) {
            $query->where('match_games.competition_id', $selectedCompetition);
        }

        $scorers = $query->orderByDesc('total_goals')
            ->orderByDesc('penalty_goals')
            ->with(['player', 'team'])
            ->limit(50)
            ->get();

        // Add ranking
        $rank = 1;
        $prevGoals = null;
        foreach ($scorers as $i => $scorer) {
            if ($prevGoals !== null && $scorer->total_goals < $prevGoals) {
                $rank = $i + 1;
            }
            $scorer->rank = $rank;
            $prevGoals = $scorer->total_goals;
        }

        return view('top-scorers', compact('competitions', 'selectedCompetition', 'scorers'));
    }
}
