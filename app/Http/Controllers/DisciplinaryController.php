<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\DisciplinaryFine;
use App\Models\MatchGame;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisciplinaryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $fines = DisciplinaryFine::with(['team', 'player', 'competition', 'matchGame', 'issuedByUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalPending = DisciplinaryFine::where('status', 'pending')->sum('amount');
        $totalPaid = DisciplinaryFine::where('status', 'paid')->sum('amount');

        return view('disciplinary.index', compact('fines', 'totalPending', 'totalPaid'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $competitions = Competition::orderBy('name')->get();
        $teams = Team::orderBy('name')->get();

        return view('disciplinary.create', compact('competitions', 'teams'));
    }

    public function getPlayers($teamId)
    {
        $players = Player::where('team_id', $teamId)->orderBy('name')->get(['id', 'name', 'jersey_number']);
        return response()->json($players);
    }

    public function getMatches($competitionId)
    {
        $matches = MatchGame::where('competition_id', $competitionId)
            ->with(['homeTeam:id,name', 'awayTeam:id,name'])
            ->orderByDesc('match_date')
            ->get(['id', 'home_team_id', 'away_team_id', 'match_date', 'match_code']);
        return response()->json($matches);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['nullable', 'exists:players,id'],
            'competition_id' => ['required', 'exists:competitions,id'],
            'match_game_id' => ['nullable', 'exists:match_games,id'],
            'fine_type' => ['required', 'in:red_card,yellow_accumulation,misconduct,late_arrival,walkover,other'],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $fine = DisciplinaryFine::create([
            'team_id' => $validated['team_id'],
            'player_id' => $validated['player_id'] ?? null,
            'competition_id' => $validated['competition_id'],
            'match_game_id' => $validated['match_game_id'] ?? null,
            'issued_by' => $user->id,
            'fine_type' => $validated['fine_type'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'payment_url' => $validated['payment_url'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fine_issued_success'));
    }

    public function markAsPaid($fineId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $fine = DisciplinaryFine::findOrFail($fineId);
        $fine->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'manual',
            'notes' => ($fine->notes ? $fine->notes . "\n" : '') . 'Marked as paid by ' . $user->name . ' on ' . now()->format('d/m/Y H:i'),
        ]);

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fine_marked_paid'));
    }

    public function waive($fineId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $fine = DisciplinaryFine::findOrFail($fineId);
        $fine->update([
            'status' => 'waived',
            'notes' => ($fine->notes ? $fine->notes . "\n" : '') . 'Waived by ' . $user->name . ' on ' . now()->format('d/m/Y H:i'),
        ]);

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fine_waived'));
    }

    public function myFines()
    {
        $user = Auth::user();

        $fines = DisciplinaryFine::with(['team', 'player', 'competition', 'matchGame'])
            ->where(function ($query) use ($user) {
                $query->where('team_id', $user->team_id);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalPending = DisciplinaryFine::where('team_id', $user->team_id)
            ->where('status', 'pending')
            ->sum('amount');

        return view('disciplinary.my-fines', compact('fines', 'totalPending'));
    }

    public function destroy($fineId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $fine = DisciplinaryFine::findOrFail($fineId);
        $fine->delete();

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fine_deleted'));
    }
}
