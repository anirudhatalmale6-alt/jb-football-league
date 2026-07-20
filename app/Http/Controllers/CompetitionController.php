<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\KnockoutMatch;
use App\Models\Group;
use App\Models\Standing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetitionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());

        if ($isAdmin) {
            $competitions = Competition::withCount(['teams', 'matchGames'])
                ->orderByDesc('created_at')
                ->paginate(12);
        } else {
            $competitions = Competition::withCount([
                'teams' => function ($q) { $q->where('status', 'approved'); },
                'matchGames'
            ])->orderByDesc('created_at')->paginate(12);
        }

        return view('competitions.index', compact('competitions'));
    }

    public function create()
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        return view('competitions.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'season' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:league,cup,friendly'],
            'status' => ['required', 'in:upcoming,active,completed'],
            'match_duration' => ['nullable', 'integer', 'min:30', 'max:130'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'max_players' => ['nullable', 'integer', 'min:1'],
            'max_officials' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos/competitions', 'public');
        }

        Competition::create($validated);

        return redirect()->route('competitions.index')
            ->with('success', 'Competition created successfully.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());

        if ($isAdmin) {
            $competition = Competition::with(['teams.group', 'matchGames.homeTeam', 'matchGames.awayTeam', 'groups.teams'])->findOrFail($id);
        } else {
            // Non-admin: only show approved teams
            $competition = Competition::with(['matchGames.homeTeam', 'matchGames.awayTeam'])->findOrFail($id);
            $competition->setRelation('teams', $competition->teams()->where('status', 'approved')->with('group')->get());
            $competition->load(['groups' => function ($q) {
                $q->with(['teams' => function ($q2) {
                    $q2->where('status', 'approved');
                }]);
            }]);
        }

        $standings = Standing::with('team')
            ->where('competition_id', $competition->id)
            ->orderBy('position')
            ->get();

        // Knockout bracket data
        $bracket = [];
        $champion = null;
        $bracketInitialized = false;
        if ($competition->type === 'knockout') {
            $knockoutMatches = KnockoutMatch::where('competition_id', $competition->id)
                ->with(['homeTeam', 'awayTeam', 'matchGame', 'winnerTeam'])
                ->orderBy('round')
                ->orderBy('position')
                ->get();

            $rounds = ['round_of_16', 'quarter_final', 'semi_final', 'final'];
            foreach ($rounds as $round) {
                $bracket[$round] = $knockoutMatches->where('round', $round)->sortBy('position')->values();
            }

            $bracketInitialized = $knockoutMatches->isNotEmpty();
            $finalMatch = $bracket['final']->first() ?? null;
            if ($finalMatch && $finalMatch->winner_team_id) {
                $champion = $finalMatch->winnerTeam;
            }
        }

        return view('competitions.show', compact('competition', 'standings', 'bracket', 'champion', 'bracketInitialized'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competition = Competition::findOrFail($id);

        return view('competitions.edit', compact('competition'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competition = Competition::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'season' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:league,cup,friendly'],
            'status' => ['required', 'in:upcoming,active,completed'],
            'match_duration' => ['nullable', 'integer', 'min:30', 'max:130'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'max_players' => ['nullable', 'integer', 'min:1'],
            'max_officials' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos/competitions', 'public');
        }

        $competition->update($validated);

        return redirect()->route('competitions.show', $competition->id)
            ->with('success', 'Competition updated successfully.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competition = Competition::findOrFail($id);
        $competition->delete();

        return redirect()->route('competitions.index')
            ->with('success', 'Competition deleted successfully.');
    }

    public function storeGroup($competitionId, Request $request)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $competition = Competition::findOrFail($competitionId);

        $maxOrder = $competition->groups()->max('order') ?? 0;

        $competition->groups()->create([
            'name' => $validated['name'],
            'order' => $maxOrder + 1,
        ]);

        return redirect()->route('competitions.show', $competition->id)
            ->with('success', 'Group created successfully.');
    }

    public function deleteGroup($competitionId, $groupId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competition = Competition::findOrFail($competitionId);
        $group = $competition->groups()->findOrFail($groupId);
        $group->delete();

        return redirect()->route('competitions.show', $competition->id)
            ->with('success', 'Group deleted successfully.');
    }
}
