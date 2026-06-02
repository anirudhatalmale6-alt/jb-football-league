<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchGame;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;

class DashboardController extends Controller
{
    public function index()
    {
        $competitionCount = Competition::count();
        $teamCount = Team::count();
        $playerCount = Player::count();
        $upcomingMatchCount = MatchGame::where('status', 'scheduled')
            ->where('match_date', '>=', now())
            ->count();

        $recentMatches = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
            ->where('status', 'completed')
            ->orderByDesc('match_date')
            ->limit(10)
            ->get();

        $topCompetition = Competition::where('status', 'active')->first();
        $standings = collect();

        if ($topCompetition) {
            $standings = Standing::with('team')
                ->where('competition_id', $topCompetition->id)
                ->orderBy('position')
                ->get();
        }

        return view('dashboard', compact(
            'competitionCount',
            'teamCount',
            'playerCount',
            'upcomingMatchCount',
            'recentMatches',
            'standings',
            'topCompetition'
        ));
    }
}
