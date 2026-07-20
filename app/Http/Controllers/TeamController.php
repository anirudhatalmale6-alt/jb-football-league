<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Group;
use App\Models\Team;
use App\Models\RegistrationPayment;
use App\Models\TeamStatusLog;
use App\Models\PromotionOffer;
use App\Models\User;
use App\Mail\TeamApprovedMail;
use App\Mail\TeamRejectedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with('competition')->withCount(['players', 'officials']);
        $user = Auth::user();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('competition_id') || $request->filled('competition')) {
            $query->where('competition_id', $request->input('competition_id', $request->input('competition')));
        }

        if ($user && ($user->isSuper() || $user->isLeagueAdmin())) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'approved');
        }

        $competitions = Competition::orderBy('name')->get();

        // Group duplicate team records (same club entered in more than one
        // competition) into a single profile, so each team appears only once
        // with all its competitions linked. Applies to admin and non-admin.
        $allTeams = $query->orderBy('name')->get();
        $grouped = $allTeams->groupBy(fn($t) => mb_strtolower(trim($t->name)));

        $uniqueTeams = $grouped->map(function ($group) {
            // Primary = the most complete record (has logo + players + a league entry).
            $primary = $group->sortByDesc(function ($t) {
                return ($t->logo ? 4 : 0)
                    + ($t->players_count > 0 ? 2 : 0)
                    + ($t->competition && $t->competition->type === 'league' ? 1 : 0);
            })->first();
            $primary->players_count = $group->max('players_count');
            $primary->officials_count = $group->max('officials_count');
            $primary->all_competitions = $group->sortBy(fn($t) => $t->competition && $t->competition->type === 'league' ? 0 : 1)->values();
            return $primary;
        })->sortBy('name')->values();

        $isAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());
        $page = $request->input('page', 1);
        $perPage = $isAdmin ? 20 : 12;
        $teams = new LengthAwarePaginator(
            $uniqueTeams->slice(($page - 1) * $perPage, $perPage)->values(),
            $uniqueTeams->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('teams.index', compact('teams', 'competitions'));
    }

    public function create()
    {
        $competitions = Competition::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();

        return view('teams.create', compact('competitions', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'manager_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Force uppercase on name fields
        $validated['name'] = mb_strtoupper($validated['name']);
        $validated['short_name'] = mb_strtoupper($validated['short_name']);
        $validated['manager_name'] = mb_strtoupper($validated['manager_name']);

        $validated['status'] = 'pending';

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team registration submitted and pending approval.');
    }

    public function show($id)
    {
        $team = Team::with(['players', 'officials', 'competition', 'statusLogs.changedByUser', 'managers'])->findOrFail($id);

        $payment = RegistrationPayment::where('team_id', $team->id)->latest()->first();

        // For Super/League admins: list of team-manager accounts that can be
        // linked to this team (not already linked). Used by the Assign Manager
        // tool on the team page.
        $assignableManagers = collect();
        $viewer = Auth::user();
        if ($viewer && ($viewer->isSuper() || $viewer->isLeagueAdmin())) {
            $linkedIds = $team->managers->pluck('id')->all();
            $assignableManagers = User::where('role', 'team_manager')
                ->when($linkedIds, fn($q) => $q->whereNotIn('id', $linkedIds))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        $promotionOffer = PromotionOffer::with(['fromCompetition', 'toCompetition'])
            ->where('team_id', $team->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->latest()
            ->first();

        // All competition entries for this club (same name across competitions),
        // so the team profile can show one "Competition Participation" section.
        $participations = Team::with('competition')
            ->withCount(['players', 'officials'])
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($team->name))])
            ->get()
            ->sortBy(fn($t) => $t->competition && $t->competition->type === 'league' ? 0 : 1)
            ->values();

        return view('teams.show', compact('team', 'payment', 'promotionOffer', 'participations', 'assignableManagers'));
    }

    /**
     * Link a team-manager account to this team so the manager can register
     * players for it. Super Admin / League Admin only.
     */
    public function assignManager(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $manager = User::findOrFail($validated['user_id']);
        if ($manager->role !== 'team_manager') {
            return back()->with('error', __('app.manager_must_be_tm'));
        }

        // Idempotent link (no duplicate pivot rows).
        $team->managers()->syncWithoutDetaching([$manager->id]);

        // Mirror the primary team on the user account when they don't have one,
        // matching what the self-registration flow does.
        if (empty($manager->team_id)) {
            $manager->update(['team_id' => $team->id]);
        }

        return back()->with('success', __('app.manager_assigned', ['name' => $manager->name]));
    }

    /**
     * Unlink a team-manager account from this team. Super Admin / League Admin only.
     */
    public function removeManager(Request $request, $id, $userId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::findOrFail($id);
        $manager = User::findOrFail($userId);

        $team->managers()->detach($manager->id);

        // Clear the primary team pointer if it pointed at this team.
        if ((int) $manager->team_id === (int) $team->id) {
            $manager->update(['team_id' => null]);
        }

        return back()->with('success', __('app.manager_removed', ['name' => $manager->name]));
    }

    public function edit($id)
    {
        $team = Team::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        $competitions = Competition::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();

        return view('teams.edit', compact('team', 'competitions', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'manager_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Force uppercase on name fields
        $validated['name'] = mb_strtoupper($validated['name']);
        $validated['short_name'] = mb_strtoupper($validated['short_name']);
        $validated['manager_name'] = mb_strtoupper($validated['manager_name']);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // If team was rejected and manager edits, set back to pending for re-review
        if ($team->status === 'rejected' && !$user->isSuper() && !$user->isLeagueAdmin()) {
            $validated['status'] = 'pending';
            $validated['rejection_reason'] = null;
            $validated['resubmitted_at'] = now();
        }

        $team->update($validated);

        return redirect()->route('teams.show', $team->id)
            ->with('success', __('app.team_updated'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isSuper()) {
            abort(403);
        }

        $team = Team::findOrFail($id);
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    public function approve($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::findOrFail($id);
        $oldStatus = $team->status;
        $team->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);
        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'approved',
            'reason' => 'Team approved',
        ]);

        $this->sendApprovalEmail($team);

        return redirect()->route('teams.show', $team->id)
            ->with('success', __('app.team_approved'));
    }

    public function reject(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $team = Team::findOrFail($id);
        $oldStatus = $team->status;
        $team->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'rejected',
            'reason' => $request->rejection_reason,
        ]);

        $this->sendRejectionEmail($team);

        return redirect()->route('teams.show', $team->id)
            ->with('success', __('app.team_rejected', ['team' => $team->name]));
    }

    public function withdraw($id)
    {
        $team = Team::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($team->id)) {
            abort(403);
        }

        $team->update(['status' => 'withdrawn']);

        return redirect()->route('teams.show', $team->id)
            ->with('success', __('app.team_withdrawn', ['team' => $team->name]));
    }

    public function changeStatus(Request $request, $id)
    {
        if (!Auth::user()->isSuper()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,withdrawn'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $team = Team::findOrFail($id);
        $oldStatus = $team->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return redirect()->route('teams.show', $team->id)
                ->with('info', 'Status is already ' . ucfirst($newStatus) . '.');
        }

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'rejected' && $request->reason) {
            $updateData['rejection_reason'] = $request->reason;
        } elseif ($newStatus !== 'rejected') {
            $updateData['rejection_reason'] = null;
        }

        $team->update($updateData);

        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $request->reason ?? 'Status changed by Super Admin',
        ]);

        if ($newStatus === 'approved') {
            $this->sendApprovalEmail($team);
        } elseif ($newStatus === 'rejected') {
            $this->sendRejectionEmail($team);
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', "Team status changed from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus) . ".");
    }


    private function sendApprovalEmail(Team $team): void
    {
        try {
            $team->load(['competition']);
            $email = $team->contact_email;
            if ($email) {
                Mail::to($email)->send(new TeamApprovedMail($team));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send approval email for team ' . $team->id . ': ' . $e->getMessage());
        }
    }


    public function updateRejectionReason(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $team = Team::findOrFail($id);

        if ($team->status !== 'rejected') {
            return redirect()->route('teams.show', $team->id)
                ->with('error', 'Team is not in rejected status.');
        }

        $oldReason = $team->rejection_reason;
        $team->update([
            'rejection_reason' => $request->rejection_reason,
        ]);

        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => 'rejected',
            'new_status' => 'rejected',
            'reason' => 'Rejection reason updated: ' . $request->rejection_reason,
        ]);

        if ($request->has('resend_email')) {
            $this->sendRejectionEmail($team);
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Rejection reason updated' . ($request->has('resend_email') ? ' and email resent.' : '.'));
    }

    private function sendRejectionEmail(Team $team): void
    {
        try {
            $team->load(['competition']);
            $email = $team->contact_email;
            if ($email) {
                Mail::to($email)->send(new TeamRejectedMail($team));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send rejection email for team ' . $team->id . ': ' . $e->getMessage());
        }
    }

}
