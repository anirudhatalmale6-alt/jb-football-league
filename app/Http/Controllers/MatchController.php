<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\MatchEvent;
use App\Models\MatchGame;
use App\Models\MatchLineup;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchGame::with(['homeTeam', 'awayTeam', 'competition']);

        if ($request->filled('competition_id') || $request->filled('competition')) {
            $query->where('competition_id', $request->input('competition_id', $request->input('competition')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $matches = $query->orderByDesc('match_date')->paginate(15)->withQueryString();
        $competitions = Competition::orderBy('name')->get();

        return view('matches.index', compact('matches', 'competitions'));
    }

    public function create()
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $competitions = Competition::orderBy('name')->get();
        $teams = Team::where('status', 'approved')->orderBy('name')->get();

        return view('matches.create', compact('competitions', 'teams'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'matchday' => ['nullable', 'integer', 'min:1'],
            'match_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:scheduled,in_progress,completed,postponed'],
            'referee' => ['nullable', 'string', 'max:255'],
            'assistant_referee_1' => ['nullable', 'string', 'max:255'],
            'assistant_referee_2' => ['nullable', 'string', 'max:255'],
            'fourth_official' => ['nullable', 'string', 'max:255'],
            'match_commissioner' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        MatchGame::create($validated);

        return redirect()->route('matches.index')
            ->with('success', 'Match created successfully.');
    }

    public function show($id)
    {
        $match = MatchGame::with([
            'homeTeam',
            'awayTeam',
            'competition',
            'lineups.player',
            'lineups.team',
            'events.player',
            'events.team',
        ])->findOrFail($id);

        return view('matches.show', compact('match'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);
        $competitions = Competition::orderBy('name')->get();
        $teams = Team::where('status', 'approved')->orderBy('name')->get();

        return view('matches.edit', compact('match', 'competitions', 'teams'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'matchday' => ['nullable', 'integer', 'min:1'],
            'match_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:scheduled,in_progress,completed,postponed'],
            'home_score' => ['nullable', 'integer', 'min:0'],
            'away_score' => ['nullable', 'integer', 'min:0'],
            'referee' => ['nullable', 'string', 'max:255'],
            'assistant_referee_1' => ['nullable', 'string', 'max:255'],
            'assistant_referee_2' => ['nullable', 'string', 'max:255'],
            'fourth_official' => ['nullable', 'string', 'max:255'],
            'match_commissioner' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $match->update($validated);

        return redirect()->route('matches.show', $match->id)
            ->with('success', 'Match updated successfully.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);
        $match->delete();

        return redirect()->route('matches.index')
            ->with('success', 'Match deleted successfully.');
    }

    public function lineup($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::with(['homeTeam.players', 'awayTeam.players', 'lineups.player'])->findOrFail($id);
        $homePlayers = $match->homeTeam->players ?? collect();
        $awayPlayers = $match->awayTeam->players ?? collect();
        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);

        return view('matches.lineup', compact('match', 'homePlayers', 'awayPlayers', 'homeLineup', 'awayLineup'));
    }

    public function storeLineup(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['required', 'exists:players,id'],
            'jersey_number' => ['required', 'integer', 'min:1', 'max:99'],
            'position' => ['required', 'string', 'max:50'],
        ]);

        $existing = MatchLineup::where('match_game_id', $match->id)
            ->where('player_id', $validated['player_id'])
            ->first();

        if ($existing) {
            return redirect()->route('matches.lineup', $match->id)
                ->with('error', 'Player is already in the lineup.');
        }

        MatchLineup::create([
            'match_game_id' => $match->id,
            'team_id' => $validated['team_id'],
            'player_id' => $validated['player_id'],
            'jersey_number' => $validated['jersey_number'],
            'position' => $validated['position'],
            'is_starting' => $request->boolean('is_starting'),
        ]);

        return redirect()->route('matches.lineup', $match->id)
            ->with('success', 'Player added to lineup.');
    }

    public function deleteLineup($matchId, $lineupId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($matchId);
        $lineup = MatchLineup::where('match_game_id', $match->id)->findOrFail($lineupId);
        $lineup->delete();

        return redirect()->route('matches.lineup', $match->id)
            ->with('success', 'Player removed from lineup.');
    }

    public function events($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::with([
            'homeTeam.players',
            'awayTeam.players',
            'events.player',
            'events.team',
        ])->findOrFail($id);
        $homePlayers = $match->homeTeam->players ?? collect();
        $awayPlayers = $match->awayTeam->players ?? collect();
        $events = $match->events->sortBy('minute');

        return view('matches.events', compact('match', 'homePlayers', 'awayPlayers', 'events'));
    }

    public function storeEvent(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['required', 'exists:players,id'],
            'event_type' => ['required', 'in:goal,own_goal,yellow_card,red_card,substitution,penalty_scored,penalty_missed'],
            'minute' => ['required', 'integer', 'min:1', 'max:120'],
            'extra_time_minute' => ['nullable', 'integer', 'min:1'],
            'related_player_id' => ['nullable', 'exists:players,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['match_game_id'] = $match->id;

        MatchEvent::create($validated);

        return redirect()->route('matches.events', $match->id)
            ->with('success', 'Event added successfully.');
    }

    public function deleteEvent($matchId, $eventId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($matchId);
        $event = MatchEvent::where('match_game_id', $match->id)->findOrFail($eventId);
        $event->delete();

        return redirect()->route('matches.events', $match->id)
            ->with('success', 'Event removed successfully.');
    }

    public function complete($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $match = MatchGame::findOrFail($id);

        if ($match->home_score === null || $match->away_score === null) {
            return back()->with('error', 'Please set the match scores before completing.');
        }

        DB::transaction(function () use ($match) {
            $match->update(['status' => 'completed']);

            $this->updateStanding($match->competition_id, $match->home_team_id);
            $this->updateStanding($match->competition_id, $match->away_team_id);
        });

        return redirect()->route('matches.show', $match->id)
            ->with('success', 'Match marked as completed and standings updated.');
    }

    private function updateStanding(int $competitionId, int $teamId): void
    {
        $matches = MatchGame::where('competition_id', $competitionId)
            ->where('status', 'completed')
            ->where(function ($q) use ($teamId) {
                $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId);
            })
            ->get();

        $played = $matches->count();
        $won = 0;
        $drawn = 0;
        $lost = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($matches as $m) {
            if ($m->home_team_id === $teamId) {
                $goalsFor += $m->home_score;
                $goalsAgainst += $m->away_score;
                if ($m->home_score > $m->away_score) {
                    $won++;
                } elseif ($m->home_score === $m->away_score) {
                    $drawn++;
                } else {
                    $lost++;
                }
            } else {
                $goalsFor += $m->away_score;
                $goalsAgainst += $m->home_score;
                if ($m->away_score > $m->home_score) {
                    $won++;
                } elseif ($m->away_score === $m->home_score) {
                    $drawn++;
                } else {
                    $lost++;
                }
            }
        }

        Standing::updateOrCreate(
            ['competition_id' => $competitionId, 'team_id' => $teamId],
            [
                'played' => $played,
                'won' => $won,
                'drawn' => $drawn,
                'lost' => $lost,
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'goal_difference' => $goalsFor - $goalsAgainst,
                'points' => ($won * 3) + $drawn,
            ]
        );

        $standings = Standing::where('competition_id', $competitionId)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        foreach ($standings as $index => $standing) {
            $standing->update(['position' => $index + 1]);
        }
    }
}
