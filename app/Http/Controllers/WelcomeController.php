<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchGame;
use App\Models\Standing;

class WelcomeController extends Controller
{
    public function index()
    {
        $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
            ->whereIn('status', ['scheduled','live','half_time','second_half'])
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->limit(10)
            ->get();

        $activeCompetition = Competition::where('status', 'active')->first();
        $standings = collect();

        if ($activeCompetition) {
            $standings = Standing::with('team')
                ->where('competition_id', $activeCompetition->id)
                ->orderBy('position')
                ->get();
        }

        return view('welcome', compact('upcomingMatches', 'standings', 'activeCompetition'));
    }
}
