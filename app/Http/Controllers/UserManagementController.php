<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('team');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhere('club_team', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $teams = Team::with('competition')->orderBy('name')->get();

        $stats = [
            'total' => User::count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
            'league_admin' => User::where('role', 'league_admin')->count(),
            'head_match_commissioner' => User::where('role', 'head_match_commissioner')->count(),
            'match_commissioner' => User::where('role', 'match_commissioner')->count(),
            'team_manager' => User::where('role', 'team_manager')->count(),
            'public' => User::where('role', 'public')->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'teams', 'stats'));
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $request->validate([
            'role' => 'required|in:super_admin,league_admin,head_match_commissioner,match_commissioner,team_manager,public',
        ]);

        $oldRole = str_replace('_', ' ', ucwords($user->role, '_'));
        $newRole = str_replace('_', ' ', ucwords($request->role, '_'));
        $user->update(['role' => $request->role]);

        return back()->with('success', $user->name . ' role changed from ' . $oldRole . ' to ' . $newRole . '.');
    }

    public function assignTeam(Request $request, User $user)
    {
        $request->validate([
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $user->update(['team_id' => $request->team_id]);
        if ($request->team_id) {
            $user->managedTeams()->syncWithoutDetaching([$request->team_id]);
        }

        $teamName = $request->team_id ? Team::find($request->team_id)->name : 'None';
        return back()->with('success', $user->name . ' assigned to ' . $teamName . '.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'enabled' : 'disabled';
        return back()->with('success', $user->name . ' account ' . $status . '.');
    }

    public function verifyEmail(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return back()->with('info', $user->name . ' email is already verified.');
        }

        $user->markEmailAsVerified();

        return back()->with('success', $user->name . ' email has been manually verified.');
    }

    public function updateName(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update(['name' => $request->name]);

        return back()->with('success', 'Name updated to ' . $user->name . '.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuper()) {
            return back()->with('error', 'Cannot delete a Super Admin account.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', 'User ' . $name . ' deleted.');
    }
}
