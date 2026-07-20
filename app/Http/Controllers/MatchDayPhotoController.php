<?php

namespace App\Http\Controllers;

use App\Models\MatchDayPhoto;
use App\Models\MatchGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Match Day Photos - a simple, private photo record for every match.
 * Super Admin and League Admin / Match Commissioner only. No verification.
 */
class MatchDayPhotoController extends Controller
{
    /** Only match operators (admins / commissioners) may access. */
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->canOperateMatches()) {
            abort(403);
        }
    }

    /** How many of the required photos are in place + completion flag. */
    private function progress(MatchGame $match): array
    {
        $have = MatchDayPhoto::where('match_game_id', $match->id)
            ->whereIn('category', MatchDayPhoto::CATEGORIES)
            ->distinct('category')->count('category');
        $total = count(MatchDayPhoto::CATEGORIES);
        return ['uploaded' => $have, 'total' => $total, 'complete' => $have >= $total];
    }

    /** Management page: the three required photos with upload / view / replace. */
    public function index($matchId)
    {
        $this->authorizeAccess();

        $match = MatchGame::with(['homeTeam', 'awayTeam', 'competition', 'matchDayPhotos.uploadedByUser'])
            ->findOrFail($matchId);

        $photos = $match->matchDayPhotos->keyBy('category');

        return view('match-photos.index', compact('match', 'photos'));
    }

    /** Upload or replace a photo for one category (same endpoint for both). */
    public function upload(Request $request, $matchId)
    {
        $this->authorizeAccess();

        $match = MatchGame::findOrFail($matchId);

        if (!$match->canEditBy(Auth::user())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => __('app.match_locked_no_edit')], 422);
            }
            return back()->with('error', __('app.match_locked_no_edit'));
        }

        $validated = $request->validate([
            'category' => ['required', 'in:' . implode(',', MatchDayPhoto::CATEGORIES)],
            'photo' => ['required', 'image', 'max:10240'],
        ]);

        $category = $validated['category'];

        // Derive the team for home/away categories; referee_captains has no team.
        $teamId = match ($category) {
            MatchDayPhoto::CATEGORY_HOME_XI => $match->home_team_id,
            MatchDayPhoto::CATEGORY_AWAY_XI => $match->away_team_id,
            default => null,
        };

        // Store on the PRIVATE local disk (never publicly reachable by URL).
        $path = $request->file('photo')->store('match-day-photos/' . $match->id, 'local');

        $existing = MatchDayPhoto::where('match_game_id', $match->id)
            ->where('category', $category)
            ->first();

        // Remove the old file when replacing.
        if ($existing && $existing->photo && Storage::disk('local')->exists($existing->photo)) {
            Storage::disk('local')->delete($existing->photo);
        }

        MatchDayPhoto::updateOrCreate(
            ['match_game_id' => $match->id, 'category' => $category],
            [
                'team_id' => $teamId,
                'photo' => $path,
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now(),
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge(['ok' => true, 'category' => $category], $this->progress($match)));
        }

        return redirect()->route('match-photos.index', $match->id)
            ->with('success', __('app.mdp_upload_success', ['category' => __('app.' . MatchDayPhoto::categoryLangKey($category))]));
    }

    /** Stream the stored photo to authorised users only (keeps it private). */
    public function file($matchId, $category): StreamedResponse
    {
        $this->authorizeAccess();

        $photo = MatchDayPhoto::where('match_game_id', $matchId)
            ->where('category', $category)
            ->firstOrFail();

        if (!$photo->photo || !Storage::disk('local')->exists($photo->photo)) {
            abort(404);
        }

        return Storage::disk('local')->response($photo->photo);
    }

    /** Remove a wrongly-uploaded photo. */
    public function destroy(Request $request, $matchId, $category)
    {
        $this->authorizeAccess();

        $match = MatchGame::findOrFail($matchId);
        if (!$match->canEditBy(Auth::user())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => __('app.match_locked_no_edit')], 422);
            }
            return back()->with('error', __('app.match_locked_no_edit'));
        }

        $photo = MatchDayPhoto::where('match_game_id', $matchId)
            ->where('category', $category)
            ->firstOrFail();

        if ($photo->photo && Storage::disk('local')->exists($photo->photo)) {
            Storage::disk('local')->delete($photo->photo);
        }
        $photo->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge(['ok' => true, 'category' => $category], $this->progress($match)));
        }

        return redirect()->route('match-photos.index', $matchId)
            ->with('success', __('app.mdp_removed'));
    }
}
