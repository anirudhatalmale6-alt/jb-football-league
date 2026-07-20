<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchGame;

class LigaInfoController extends Controller
{
    public function index()
    {
        $competitions = Competition::withCount(['teams', 'groups'])
            ->orderBy('name')
            ->get();

        $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
            ->where('status', 'scheduled')
            ->orderBy('match_date')
            ->limit(10)
            ->get();

        return view('liga-info.index', compact('competitions', 'upcomingMatches'));
    }
}
