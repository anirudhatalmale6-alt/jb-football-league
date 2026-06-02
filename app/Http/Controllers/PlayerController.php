<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::with('team');

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $players = $query->orderBy('name')->paginate(20)->withQueryString();
        $teams = Team::orderBy('name')->get();

        return view('players.index', compact('players', 'teams'));
    }

    public function create()
    {
        $teams = Team::where('status', 'approved')->orderBy('name')->get();

        return view('players.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'jersey_number' => ['required', 'integer', 'min:1', 'max:99'],
            'position' => ['required', 'in:goalkeeper,defender,midfielder,forward'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nationality' => ['required', 'string', 'max:100'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:active,injured,suspended,inactive'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('players', 'public');
        }

        Player::create($validated);

        return redirect()->route('players.index')
            ->with('success', 'Player registered successfully.');
    }

    public function show($id)
    {
        $player = Player::with('team')->findOrFail($id);

        return view('players.show', compact('player'));
    }

    public function edit($id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && $user->team_id !== $player->team_id) {
            abort(403);
        }

        $teams = Team::where('status', 'approved')->orderBy('name')->get();

        return view('players.edit', compact('player', 'teams'));
    }

    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && $user->team_id !== $player->team_id) {
            abort(403);
        }

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'jersey_number' => ['required', 'integer', 'min:1', 'max:99'],
            'position' => ['required', 'in:goalkeeper,defender,midfielder,forward'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nationality' => ['required', 'string', 'max:100'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:active,injured,suspended,inactive'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('players', 'public');
        }

        $player->update($validated);

        return redirect()->route('players.show', $player->id)
            ->with('success', 'Player updated successfully.');
    }

    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && $user->team_id !== $player->team_id) {
            abort(403);
        }

        $player->delete();

        return redirect()->route('players.index')
            ->with('success', 'Player deleted successfully.');
    }
}
