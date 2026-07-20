<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{

    private function removeBackground(string $storedPath): ?string
    {
        $fullPath = storage_path("app/public/{$storedPath}");
        if (!file_exists($fullPath)) return null;

        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
        $bgRemovedName = "players/bg_" . uniqid() . ".png";
        $outputPath = storage_path("app/public/{$bgRemovedName}");

        $escapedInput = escapeshellarg($fullPath);
        $escapedOutput = escapeshellarg($outputPath);
        $cmd = "python3 -c \"from rembg import remove; from PIL import Image; import io; img = Image.open({$escapedInput}); out = remove(img); out.save({$escapedOutput})\" 2>&1";

        exec($cmd, $output, $code);

        if ($code === 0 && file_exists($outputPath)) {
            return $bgRemovedName;
        }
        return null;
    }

    private function deriveDobFromIc(?string $icNumber): ?string
    {
        if (!$icNumber) return null;
        $ic = preg_replace('/[^0-9]/', '', $icNumber);
        if (strlen($ic) < 6) return null;

        $yy = (int)substr($ic, 0, 2);
        $mm = (int)substr($ic, 2, 2);
        $dd = (int)substr($ic, 4, 2);

        if (!checkdate($mm, $dd, 2000)) return null;

        $year = (2000 + $yy <= (int)date('Y')) ? 2000 + $yy : 1900 + $yy;

        try {
            $dob = Carbon::createFromDate($year, $mm, $dd);
            if ($dob->isFuture()) return null;
            return $dob->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function detectFlags(Player $player): array
    {
        $flags = [];

        if (empty($player->photo)) {
            $flags[] = 'Missing player photo';
        }
        if (empty($player->ic_number)) {
            $flags[] = 'Missing IC number';
        }
        if (empty($player->ic_photo)) {
            $flags[] = 'Missing IC image';
        }

        // Duplicate IC check.
        // The SAME player may play for the SAME club across different competitions
        // (e.g. League + FA Cup) — that is allowed and NOT flagged. A duplicate is
        // only a real problem when the same IC is used under a DIFFERENT club.
        if (!empty($player->ic_number)) {
            $icClean = preg_replace('/[^0-9]/', '', $player->ic_number);
            $myTeamName = $player->team ? mb_strtolower(trim($player->team->name)) : null;
            $conflict = Player::where('id', '!=', $player->id)
                ->where(function ($q) use ($player, $icClean) {
                    $q->where('ic_number', $player->ic_number)
                       ->orWhereRaw("REPLACE(REPLACE(ic_number, '-', ''), ' ', '') = ?", [$icClean]);
                })
                ->with('team')
                ->get()
                ->first(function ($dup) use ($myTeamName) {
                    $dupTeamName = $dup->team ? mb_strtolower(trim($dup->team->name)) : null;
                    return $dupTeamName !== $myTeamName;
                });
            if ($conflict) {
                $flags[] = 'Duplicate IC - also registered under another team (' . ($conflict->team->name ?? '-') . ')';
            }
        }

        return $flags;
    }

    /**
     * Decide whether an IC number may be registered for the given team.
     * Returns an error message string when it must be blocked, or null when OK.
     *
     * Rules:
     *   - Different club with the same IC  -> block (real duplicate).
     *   - Same team (same club+competition) -> block (already registered here).
     *   - Same club, different competition  -> allowed (reuse squad), null.
     */
    private function icConflictError(string $icNumber, int $teamId, ?int $excludePlayerId = null): ?string
    {
        $icClean = preg_replace('/[^0-9]/', '', $icNumber);
        $targetTeam = Team::find($teamId);
        $targetTeamName = $targetTeam ? mb_strtolower(trim($targetTeam->name)) : null;

        $query = Player::where(function ($q) use ($icNumber, $icClean) {
            $q->where('ic_number', $icNumber)
               ->orWhereRaw("REPLACE(REPLACE(ic_number, '-', ''), ' ', '') = ?", [$icClean]);
        })->with('team');

        if ($excludePlayerId) {
            $query->where('id', '!=', $excludePlayerId);
        }

        $icMatches = $query->get();

        // (a) Same IC under a DIFFERENT club -> real duplicate, block.
        $otherClub = $icMatches->first(function ($p) use ($targetTeamName) {
            $n = $p->team ? mb_strtolower(trim($p->team->name)) : null;
            return $n !== $targetTeamName;
        });
        if ($otherClub) {
            return 'No. IC ini telah didaftarkan atas pasukan lain (' . ($otherClub->team->name ?? '-')
                . '). Sila semak semula. / This IC is already registered under another team ('
                . ($otherClub->team->name ?? '-') . ').';
        }

        // (b) Same IC already registered for THIS exact team -> prevent duplicate row.
        $sameTeam = $icMatches->firstWhere('team_id', $teamId);
        if ($sameTeam) {
            return 'Pemain ini telah pun didaftarkan untuk pasukan ini. / This player is already registered for this team.';
        }

        // (c) Same club, different competition -> allowed.
        return null;
    }

    public function index(Request $request)
    {
        $query = Player::with('team.competition');
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());

        // Team Manager can only see their own team's players by default
        if ($user && $user->isTeamManager() && $user->hasTeams()) {
            $query->whereIn('team_id', $user->managedTeamIds());
        } elseif ($request->filled('team_id')) {
            $filterTeam = Team::find($request->team_id);
            if ($filterTeam) {
                $sameNameIds = Team::where('name', $filterTeam->name)->pluck('id');
                $query->whereIn('team_id', $sameNameIds);
            } else {
                $query->where('team_id', $request->team_id);
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $all = $query->orderBy('name')->get();

        // Collapse the SAME player (same IC, or same name+jersey) under the SAME club
        // into one row, listing every competition they play in. A club that joins
        // both the League and the FA Cup reuses one squad, so each player appears once.
        $grouped = $all->groupBy(function ($p) {
            $ic = $p->ic_number ? preg_replace('/[^0-9]/', '', $p->ic_number) : '';
            $identity = $ic !== '' ? 'ic:' . $ic : 'nm:' . mb_strtolower(trim($p->name)) . '#' . $p->jersey_number;
            $team = $p->team ? mb_strtolower(trim($p->team->name)) : 'noteam';
            return $identity . '@' . $team;
        })->map(function ($group) {
            // Keep the most complete record as the one shown.
            $primary = $group->sortByDesc(function ($p) {
                return (!empty($p->photo) ? 4 : 0) + (!empty($p->ic_photo) ? 2 : 0) + (!empty($p->ic_number) ? 1 : 0);
            })->first();
            $primary->_competitions = $group
                ->map(fn ($p) => optional(optional($p->team)->competition)->name)
                ->filter()->unique()->values();
            return $primary;
        })->sortBy('name')->values();

        // Flag detection (admin only) done in-memory to avoid a query per player.
        $showFlaggedOnly = false;
        if ($isAdmin) {
            $icTeamMap = [];
            foreach ($all as $p) {
                if (empty($p->ic_number)) continue;
                $ic = preg_replace('/[^0-9]/', '', $p->ic_number);
                if ($ic === '') continue;
                $tn = $p->team ? mb_strtolower(trim($p->team->name)) : '';
                $icTeamMap[$ic][$tn] = true;
            }

            foreach ($grouped as $player) {
                $player->_flags = $this->flagsFromMap($player, $icTeamMap);
            }

            if ($request->filled('flagged')) {
                $showFlaggedOnly = true;
                $grouped = $grouped->filter(function ($p) {
                    return !empty($p->_flags) || $p->verification_status === 'flagged';
                })->values();
            }
        }

        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $players = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped->slice(($page - 1) * $perPage, $perPage)->values(),
            $grouped->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $teams = Team::where('status', 'approved')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        return view('players.index', compact('players', 'teams', 'isAdmin', 'showFlaggedOnly'));
    }

    /** Fast flag builder for the list view, using a prebuilt IC -> clubs map. */
    private function flagsFromMap(Player $player, array $icTeamMap): array
    {
        $flags = [];
        if (empty($player->photo)) $flags[] = 'Missing player photo';
        if (empty($player->ic_number)) $flags[] = 'Missing IC number';
        if (empty($player->ic_photo)) $flags[] = 'Missing IC image';

        if (!empty($player->ic_number)) {
            $ic = preg_replace('/[^0-9]/', '', $player->ic_number);
            $myTeam = $player->team ? mb_strtolower(trim($player->team->name)) : '';
            $clubs = array_keys($icTeamMap[$ic] ?? []);
            $otherClubs = array_filter($clubs, fn ($c) => $c !== $myTeam);
            if (!empty($otherClubs)) {
                $flags[] = 'Duplicate IC - also registered under another team';
            }
        }

        return $flags;
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->isTeamManager()) {
            if (!$user->hasTeams()) {
                return redirect()->route('teams.index')
                    ->with('error', 'You are not assigned to any team. Please contact JBFA Admin.');
            }
            $teams = Team::with('competition')->whereIn('id', $user->managedTeamIds())->where('status', 'approved')->orderBy('name')->get()
                ->groupBy('name')
                ->map(function ($group) {
                    return $group->sortBy(fn($t) => $t->competition && $t->competition->type === 'league' ? 0 : 1)->first();
                })
                ->sortBy('name')
                ->values();
            if ($teams->isEmpty()) {
                return redirect()->route('teams.index')
                    ->with('error', 'Your team is not approved yet.');
            }
            $lockedTeam = $teams->count() === 1;
        } else {
            $teams = Team::with('competition')->where('status', 'approved')->orderBy('name')->get()
                ->groupBy('name')
                ->map(function ($group) {
                    return $group->sortBy(fn($t) => $t->competition && $t->competition->type === 'league' ? 0 : 1)->first();
                })
                ->sortBy('name')
                ->values();
            $lockedTeam = false;
        }

        return view('players.create', compact('teams', 'lockedTeam'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->isTeamManager() && !$user->managesTeam($request->team_id)) {
            abort(403, 'You can only register players for your own team.');
        }

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'jersey_number' => ['required', 'integer', 'min:1', 'max:99'],
            'position' => ['required', 'in:goalkeeper,defender,midfielder,forward'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'ic_photo' => ['nullable', 'image', 'max:2048'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Duplicate IC check.
        // Same player (IC) + same club across different competitions (League +
        // FA Cup) is ALLOWED — teams reuse the same squad. Block only when the IC
        // belongs to a DIFFERENT club, or is already registered for THIS same team.
        if (!empty($validated['ic_number'])) {
            $duplicateError = $this->icConflictError($validated['ic_number'], (int) $validated['team_id']);
            if ($duplicateError) {
                return redirect()->back()->withInput()->with('error', $duplicateError);
            }
        }

        // Auto-verify: accept player if required fields complete
        $hasIssues = empty($validated['ic_number']) || !$request->hasFile('ic_photo') || !$request->hasFile('photo');
        $validated['verification_status'] = $hasIssues ? 'flagged' : 'verified';
        $validated['status'] = 'active';

        $validated['name'] = mb_strtoupper($validated['name']);

        if (empty($validated['date_of_birth']) && !empty($validated['ic_number'])) {
            $validated['date_of_birth'] = $this->deriveDobFromIc($validated['ic_number']);
        }

        if ($request->hasFile('ic_photo')) {
            $validated['ic_photo'] = $request->file('ic_photo')->store('players/ic', 'public');
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('players', 'public');
            $bgRemoved = $this->removeBackground($validated['photo']);
            if ($bgRemoved) {
                $validated['bg_removed_photo'] = $bgRemoved;
            }
        }

        Player::create($validated);

        return redirect()->route('players.index')
            ->with('success', 'Player registered successfully.');
    }

    public function show($id)
    {
        $player = Player::with('team.competition')->findOrFail($id);
        $user = Auth::user();

        $canViewIc = false;
        if ($user) {
            if ($user->isSuper() || $user->isLeagueAdmin()) {
                $canViewIc = true;
            } elseif ($user->isTeamManager() && $user->managesTeam($player->team_id)) {
                $canViewIc = true;
            }
        }

        $isAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());
        $flags = $isAdmin ? $this->detectFlags($player) : [];

        return view('players.show', compact('player', 'canViewIc', 'isAdmin', 'flags'));
    }

    public function edit($id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($player->team_id)) {
            abort(403);
        }

        if ($user->isTeamManager()) {
            $teams = Team::with('competition')->where('id', $player->team_id)->get();
            $lockedTeam = true;
        } else {
            $teams = Team::with('competition')->where('status', 'approved')->orderBy('name')->get()
                ->groupBy('name')
                ->map(function ($group) {
                    return $group->sortBy(fn($t) => $t->competition && $t->competition->type === 'league' ? 0 : 1)->first();
                })
                ->sortBy('name')
                ->values();
            $lockedTeam = false;
        }

        return view('players.edit', compact('player', 'teams', 'lockedTeam'));
    }

    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($player->team_id)) {
            abort(403);
        }

        if ($user->isTeamManager()) {
            $request->merge(['team_id' => $player->team_id]);
        }

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'jersey_number' => ['required', 'integer', 'min:1', 'max:99'],
            'position' => ['required', 'in:goalkeeper,defender,midfielder,forward'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'ic_photo' => ['nullable', 'image', 'max:2048'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Duplicate IC check (exclude self). Same rule as registration: same club
        // across competitions is fine; block only a different club or the same team.
        if (!empty($validated['ic_number'])) {
            $duplicateError = $this->icConflictError($validated['ic_number'], (int) $validated['team_id'], $player->id);
            if ($duplicateError) {
                return redirect()->back()->withInput()->with('error', $duplicateError);
            }
        }

        $validated['name'] = mb_strtoupper($validated['name']);

        if (empty($validated['date_of_birth']) && !empty($validated['ic_number'])) {
            $validated['date_of_birth'] = $this->deriveDobFromIc($validated['ic_number']);
        }

        if ($request->hasFile('ic_photo')) {
            $validated['ic_photo'] = $request->file('ic_photo')->store('players/ic', 'public');
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('players', 'public');
            $bgRemoved = $this->removeBackground($validated['photo']);
            if ($bgRemoved) {
                $validated['bg_removed_photo'] = $bgRemoved;
            }
        }

        // Re-evaluate verification: auto-verify if now complete, flag if missing
        $icNum = $validated['ic_number'] ?? $player->ic_number;
        $icPhoto = isset($validated['ic_photo']) ? $validated['ic_photo'] : $player->ic_photo;
        $photo = isset($validated['photo']) ? $validated['photo'] : $player->photo;

        $hasIssues = empty($icNum) || empty($icPhoto) || empty($photo);
        if ($hasIssues) {
            $validated['verification_status'] = 'flagged';
        } elseif ($player->verification_status === 'flagged' || $player->verification_status === 'pending') {
            $validated['verification_status'] = 'verified';
        }

        $player->update($validated);

        return redirect()->route('players.show', $player->id)
            ->with('success', 'Player updated successfully.');
    }

    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($player->team_id)) {
            abort(403);
        }

        $player->delete();

        return redirect()->route('players.index')
            ->with('success', 'Player deleted successfully.');
    }

    public function verify(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'verification_status' => ['required', 'in:pending,verified,rejected,flagged'],
        ]);

        $player->update($validated);

        $statusText = ucfirst($validated['verification_status']);
        return redirect()->route('players.show', $player->id)
            ->with('success', "Player verification status updated to: {$statusText}");
    }

}