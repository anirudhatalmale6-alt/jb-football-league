<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\DisciplinaryFine;
use App\Models\MatchGame;
use App\Models\Player;
use App\Models\Team;
use App\Services\DisciplinarySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisciplinaryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageDiscipline()) {
            abort(403);
        }

        $query = DisciplinaryFine::with(['team', 'player', 'competition', 'matchGame', 'issuedByUser']);

        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }
        if ($request->filled('player')) {
            $term = $request->player;
            $query->whereHas('player', fn ($q) => $q->where('name', 'like', "%{$term}%"));
        }
        if ($request->filled('card_type')) {
            $query->where('card_type', $request->card_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fines = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Summary reflects the full data set (not just the current filter/page).
        $totalPending = DisciplinaryFine::where('status', 'pending')->sum('amount');
        $totalPaid = DisciplinaryFine::where('status', 'paid')->sum('amount');
        $activeSuspensions = DisciplinaryFine::where('is_suspended', true)->whereNull('suspension_lifted_at')->count();

        $competitions = Competition::orderBy('name')->get();
        $teams = Team::orderBy('name')->get();

        return view('disciplinary.index', compact('fines', 'totalPending', 'totalPaid', 'activeSuspensions', 'competitions', 'teams'));
    }

    /**
     * Backfill/refresh all auto fines from recorded match card events.
     */
    public function sync()
    {
        $user = Auth::user();
        if (!$user->canManageDiscipline()) {
            abort(403);
        }

        $count = app(DisciplinarySyncService::class)->syncAll();

        return redirect()->route('disciplinary.index')
            ->with('success', __('app.fines_synced', ['count' => $count]));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->canManageDiscipline()) {
            abort(403);
        }

        $competitions = Competition::orderBy('name')->get();
        $teams = Team::with('competition')->orderBy('name')->get();

        return view('disciplinary.create', compact('competitions', 'teams'));
    }

    public function getPlayers($teamId)
    {
        // Squad is shared at club level; read it through the team's club-keyed
        // relationship so every competition entry sees the full squad.
        $team = Team::find($teamId);
        $players = $team
            ? $team->players()->orderBy('name')->get(['id', 'name', 'jersey_number'])
            : collect();
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
        if (!$user->canManageDiscipline()) {
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
        if (!$user->canManageDiscipline()) {
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
        if (!$user->canManageDiscipline()) {
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
        if (!$user->canManageDiscipline()) {
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

        $isAdmin = $user->canManageDiscipline();
        $isOwner = $user->managesTeam($fine->team_id);
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

        $isAdmin = $user->canManageDiscipline();
        $isOwner = $user->managesTeam($fine->team_id);
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
        if (!$user->canManageDiscipline()) {
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
            ->whereIn('team_id', $user->managedTeamIds())
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalPending = DisciplinaryFine::whereIn('team_id', $user->managedTeamIds())
            ->where('status', 'pending')
            ->sum('amount');

        return view('disciplinary.my-fines', compact('fines', 'totalPending'));
    }

    public function destroy($fineId)
    {
        $user = Auth::user();
        if (!$user->canManageDiscipline()) {
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
