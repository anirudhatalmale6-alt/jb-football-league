<?php

namespace App\Http\Controllers;

use App\Models\Official;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficialController extends Controller
{
    public function create($teamId)
    {
        $team = Team::findOrFail($teamId);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !($user->isTeamManager() && $user->managesTeam($team->id))) {
            abort(403);
        }

        $roles = ['Head Coach', 'Assistant Coach', 'Team Physio', 'Team Manager', 'Goalkeeper Coach', 'Fitness Coach', 'Other'];

        return view('officials.create', compact('team', 'roles'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'team_id' => ['required', 'exists:teams,id'],
            'role' => ['required', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'ic_photo' => ['nullable', 'image', 'max:2048'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $team = Team::findOrFail($validated['team_id']);

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !($user->isTeamManager() && $user->managesTeam($team->id))) {
            abort(403);
        }

        // Force uppercase on name
        $validated['name'] = mb_strtoupper($validated['name']);

        if ($request->hasFile('ic_photo')) {
            $validated['ic_photo'] = $request->file('ic_photo')->store('officials/ic', 'public');
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('officials/photos', 'public');
        }

        if ($request->hasFile('certificate')) {
            $validated['certificate'] = $request->file('certificate')->store('officials/certificates', 'public');
        }

        Official::create($validated);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Official added successfully.');
    }

    public function edit($id)
    {
        $official = Official::with('team')->findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !($user->isTeamManager() && $user->managesTeam($official->team_id))) {
            abort(403);
        }

        $roles = ['Head Coach', 'Assistant Coach', 'Team Physio', 'Team Manager', 'Goalkeeper Coach', 'Fitness Coach', 'Other'];

        return view('officials.edit', compact('official', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $official = Official::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !($user->isTeamManager() && $user->managesTeam($official->team_id))) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'ic_photo' => ['nullable', 'image', 'max:2048'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Force uppercase on name
        $validated['name'] = mb_strtoupper($validated['name']);

        if ($request->hasFile('ic_photo')) {
            $validated['ic_photo'] = $request->file('ic_photo')->store('officials/ic', 'public');
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('officials/photos', 'public');
        }

        if ($request->hasFile('certificate')) {
            $validated['certificate'] = $request->file('certificate')->store('officials/certificates', 'public');
        }

        $official->update($validated);

        return redirect()->route('teams.show', $official->team_id)
            ->with('success', 'Official updated successfully.');
    }

    public function destroy($id)
    {
        $official = Official::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !($user->isTeamManager() && $user->managesTeam($official->team_id))) {
            abort(403);
        }

        $teamId = $official->team_id;
        $official->delete();

        return redirect()->route('teams.show', $teamId)
            ->with('success', 'Official removed successfully.');
    }
}
