<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function matchSummary($matchId)
    {
        $match = MatchGame::with([
            'homeTeam',
            'awayTeam',
            'competition',
            'events.player',
            'events.team',
            'lineups.player',
            'lineups.team',
        ])->findOrFail($matchId);

        $pdf = Pdf::loadView('pdf.match-summary', compact('match'));

        return $pdf->download('match-summary-' . $match->id . '.pdf');
    }

    public function teamSheet($matchId)
    {
        $match = MatchGame::with([
            'homeTeam',
            'awayTeam',
            'competition',
            'lineups.player',
            'lineups.team',
        ])->findOrFail($matchId);

        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);

        $pdf = Pdf::loadView('pdf.team-sheet', compact('match', 'homeLineup', 'awayLineup'));

        return $pdf->download('team-sheet-' . $match->id . '.pdf');
    }
}
