<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Group;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with('competition');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('competition_id') || $request->filled('competition')) {
            $query->where('competition_id', $request->input('competition_id', $request->input('competition')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teams = $query->orderBy('name')->paginate(12)->withQueryString();
        $competitions = Competition::orderBy('name')->get();

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
            'short_name' => ['required', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'manager_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

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
        $team = Team::with(['players', 'officials', 'competition'])->findOrFail($id);

        return view('teams.show', compact('team'));
    }

    public function edit($id)
    {
        $team = Team::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && $user->team_id !== $team->id) {
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

        if (!$user->isSuper() && !$user->isLeagueAdmin() && $user->team_id !== $team->id) {
            abort(403);
        }

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'manager_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $team->update($validated);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team updated successfully.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
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
        $team->update(['status' => 'approved']);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team approved successfully.');
    }
}
