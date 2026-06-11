<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\RegistrationPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    public function matchSummary($matchId)
    {
        $match = MatchGame::with([
            'homeTeam.players',
            'homeTeam.officials',
            'awayTeam.players',
            'awayTeam.officials',
            'competition',
            'lineups.player',
            'events.player',
            'events.team',
        ])->findOrFail($matchId);

        // Separate lineups
        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);
        $homeStarters = $homeLineup->where('is_starting', true)->sortBy('jersey_number');
        $homeSubs = $homeLineup->where('is_starting', false)->sortBy('jersey_number');
        $awayStarters = $awayLineup->where('is_starting', true)->sortBy('jersey_number');
        $awaySubs = $awayLineup->where('is_starting', false)->sortBy('jersey_number');

        // Map events to players: playerEventsMap[player_id] = [events...]
        $playerEventsMap = [];
        foreach ($match->events as $event) {
            if ($event->player_id) {
                $playerEventsMap[$event->player_id][] = $event;
            }
        }

        // Separate home/away officials by role
        $homeOfficials = $match->homeTeam->officials ?? collect();
        $awayOfficials = $match->awayTeam->officials ?? collect();

        // Competition logo as base64 for DomPDF
        $competitionLogoBase64 = null;
        if ($match->competition && $match->competition->logo) {
            $logoPath = storage_path('app/public/' . $match->competition->logo);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $competitionLogoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        $homeLogoBase64 = null;
        if ($match->homeTeam && $match->homeTeam->logo) {
            $path = storage_path('app/public/' . $match->homeTeam->logo);
            if (file_exists($path)) {
                $homeLogoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $awayLogoBase64 = null;
        if ($match->awayTeam && $match->awayTeam->logo) {
            $path = storage_path('app/public/' . $match->awayTeam->logo);
            if (file_exists($path)) {
                $awayLogoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('pdf.match-summary', compact(
            'match',
            'homeStarters',
            'homeSubs',
            'awayStarters',
            'awaySubs',
            'playerEventsMap',
            'homeOfficials',
            'awayOfficials',
            'competitionLogoBase64',
            'homeLogoBase64',
            'awayLogoBase64',
        ));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('match-summary-' . $match->id . '.pdf');
    }

    public function teamSheet($matchId)
    {
        $match = MatchGame::with([
            'homeTeam.players',
            'homeTeam.officials',
            'awayTeam.players',
            'awayTeam.officials',
            'competition',
            'lineups.player',
        ])->findOrFail($matchId);

        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);
        $homeStarters = $homeLineup->where('is_starting', true)->sortBy('jersey_number');
        $homeSubs = $homeLineup->where('is_starting', false)->sortBy('jersey_number');
        $awayStarters = $awayLineup->where('is_starting', true)->sortBy('jersey_number');
        $awaySubs = $awayLineup->where('is_starting', false)->sortBy('jersey_number');

        $homeOfficials = $match->homeTeam->officials ?? collect();
        $awayOfficials = $match->awayTeam->officials ?? collect();

        // Competition logo as base64 for DomPDF
        $competitionLogoBase64 = null;
        if ($match->competition && $match->competition->logo) {
            $logoPath = storage_path('app/public/' . $match->competition->logo);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $competitionLogoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        $homeLogoBase64 = null;
        if ($match->homeTeam && $match->homeTeam->logo) {
            $path = storage_path('app/public/' . $match->homeTeam->logo);
            if (file_exists($path)) {
                $homeLogoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $awayLogoBase64 = null;
        if ($match->awayTeam && $match->awayTeam->logo) {
            $path = storage_path('app/public/' . $match->awayTeam->logo);
            if (file_exists($path)) {
                $awayLogoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('pdf.team-sheet', compact(
            'match',
            'homeStarters',
            'homeSubs',
            'awayStarters',
            'awaySubs',
            'homeOfficials',
            'awayOfficials',
            'competitionLogoBase64',
            'homeLogoBase64',
            'awayLogoBase64',
        ));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('team-sheet-' . $match->id . '.pdf');
    }

    public function paymentReceipt($paymentId)
    {
        $payment = RegistrationPayment::with(['team', 'competition', 'user'])->findOrFail($paymentId);

        $user = Auth::user();
        $isAdmin = $user->isSuper() || $user->isLeagueAdmin();
        $isOwner = $payment->user_id === $user->id || $payment->team_id === $user->team_id;
        if (!$isAdmin && !$isOwner) {
            abort(403);
        }

        $jbfaLogoBase64 = null;
        $jbfaLogoPath = public_path('images/jbfa_logo.png');
        if (file_exists($jbfaLogoPath)) {
            $jbfaLogoBase64 = 'data:' . mime_content_type($jbfaLogoPath) . ';base64,' . base64_encode(file_get_contents($jbfaLogoPath));
        }

        $competitionLogoBase64 = null;
        if ($payment->competition && $payment->competition->logo) {
            $logoPath = storage_path('app/public/' . $payment->competition->logo);
            if (file_exists($logoPath)) {
                $competitionLogoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = Pdf::loadView('pdf.payment-receipt', compact(
            'payment',
            'jbfaLogoBase64',
            'competitionLogoBase64',
        ));

        $pdf->setPaper('a4', 'portrait');

        $receiptNo = 'JBFA-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        return $pdf->download('receipt-' . $receiptNo . '.pdf');
    }
}
