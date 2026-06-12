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
        $activeSuspensions = DisciplinaryFine::where('is_suspended', true)->whereNull('suspension_lifted_at')->count();

        return view('disciplinary.index', compact('fines', 'totalPending', 'totalPaid', 'activeSuspensions'));
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
            'is_suspended' => ['nullable'],
            'suspension_type' => ['nullable', 'in:until_paid,match_ban'],
            'suspension_matches' => ['nullable', 'integer', 'min:1'],
        ]);

        $isSuspended = $request->has('is_suspended');

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
            'is_suspended' => $isSuspended,
            'suspension_type' => $isSuspended ? ($validated['suspension_type'] ?? 'until_paid') : null,
            'suspension_matches' => $isSuspended && ($validated['suspension_type'] ?? '') === 'match_ban' ? ($validated['suspension_matches'] ?? null) : null,
        ]);

        if ($isSuspended && $fine->player_id) {
            Player::where('id', $fine->player_id)->update(['status' => 'suspended']);
        }

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
            'payment_method' => 'bank_transfer',
            'notes' => ($fine->notes ? $fine->notes . "\n" : '') . 'Marked as paid by ' . $user->name . ' on ' . now()->format('d/m/Y H:i'),
        ]);

        if ($fine->is_suspended && $fine->suspension_type === 'until_paid' && $fine->player_id) {
            $this->checkAndLiftSuspension($fine, $user->name);
        }

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fine_marked_paid'));
    }

    public function liftSuspension($fineId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $fine = DisciplinaryFine::findOrFail($fineId);

        if (!$fine->is_suspended || $fine->suspension_lifted_at) {
            return redirect()->route('disciplinary.index')
                ->with('error', __('app.suspension_already_lifted'));
        }

        $fine->update([
            'suspension_lifted_at' => now(),
            'suspension_lifted_by' => $user->name,
            'notes' => ($fine->notes ? $fine->notes . "\n" : '') . 'Suspension lifted by ' . $user->name . ' on ' . now()->format('d/m/Y H:i'),
        ]);

        if ($fine->player_id) {
            $hasOtherActive = DisciplinaryFine::where('player_id', $fine->player_id)
                ->where('id', '!=', $fine->id)
                ->where('is_suspended', true)
                ->whereNull('suspension_lifted_at')
                ->exists();

            if (!$hasOtherActive) {
                Player::where('id', $fine->player_id)->update(['status' => 'active']);
            }
        }

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.suspension_lifted'));
    }

    public function updateMatchesServed(Request $request, $fineId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $fine = DisciplinaryFine::findOrFail($fineId);
        $validated = $request->validate([
            'matches_served' => ['required', 'integer', 'min:0'],
        ]);

        $fine->update(['matches_served' => $validated['matches_served']]);

        if ($fine->suspension_matches && $validated['matches_served'] >= $fine->suspension_matches) {
            $this->checkAndLiftSuspension($fine, $user->name);
        }

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.matches_served_updated'));
    }

    public function uploadProof(Request $request, $fineId)
    {
        $user = Auth::user();
        $fine = DisciplinaryFine::findOrFail($fineId);

        $isAdmin = $user->isSuper() || $user->isLeagueAdmin();
        $isOwner = $fine->team_id === $user->team_id;
        if (!$isAdmin && !$isOwner) {
            abort(403);
        }

        $request->validate([
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('proof_file')->store('fine-proofs', 'public');
        $fine->update(['proof_file' => $path]);

        if ($isAdmin) {
            return redirect()->route('disciplinary.index')
                ->with('success', __('app.proof_uploaded'));
        }

        return redirect()->route('my.fines')
            ->with('success', __('app.proof_uploaded'));
    }

    public function viewProof($fineId)
    {
        $user = Auth::user();
        $fine = DisciplinaryFine::findOrFail($fineId);

        $isAdmin = $user->isSuper() || $user->isLeagueAdmin();
        $isOwner = $fine->team_id === $user->team_id;
        if (!$isAdmin && !$isOwner) {
            abort(403);
        }

        if (!$fine->proof_file) {
            abort(404);
        }

        $path = storage_path('app/public/' . $fine->proof_file);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
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

        if ($fine->is_suspended && $fine->suspension_type === 'until_paid' && $fine->player_id && !$fine->suspension_lifted_at) {
            $this->checkAndLiftSuspension($fine, $user->name);
        }

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

        if ($fine->is_suspended && !$fine->suspension_lifted_at && $fine->player_id) {
            $hasOtherActive = DisciplinaryFine::where('player_id', $fine->player_id)
                ->where('id', '!=', $fine->id)
                ->where('is_suspended', true)
                ->whereNull('suspension_lifted_at')
                ->exists();

            if (!$hasOtherActive) {
                Player::where('id', $fine->player_id)->update(['status' => 'active']);
            }
        }

        $fine->delete();

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fine_deleted'));
    }

    private function checkAndLiftSuspension(DisciplinaryFine $fine, string $liftedBy): void
    {
        if (!$fine->suspension_lifted_at) {
            $fine->update([
                'suspension_lifted_at' => now(),
                'suspension_lifted_by' => $liftedBy,
            ]);

            if ($fine->player_id) {
                $hasOtherActive = DisciplinaryFine::where('player_id', $fine->player_id)
                    ->where('id', '!=', $fine->id)
                    ->where('is_suspended', true)
                    ->whereNull('suspension_lifted_at')
                    ->exists();

                if (!$hasOtherActive) {
                    Player::where('id', $fine->player_id)->update(['status' => 'active']);
                }
            }
        }
    }
}
