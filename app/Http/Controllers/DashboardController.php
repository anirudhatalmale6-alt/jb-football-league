<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\LineupSubmission;
use App\Models\MatchGame;
use App\Models\MatchJersey;
use App\Models\Player;
use App\Models\RegistrationPayment;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $competitionCount = Competition::count();
        $teamCount = Team::where('status', 'approved')->distinct('name')->count('name');
        $playerCount = Player::count();
        $upcomingMatchCount = MatchGame::where('status', 'scheduled')
            ->where('match_date', '>=', now())
            ->count();

        $recentMatches = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
            ->where('status', 'completed')
            ->orderByDesc('match_date')
            ->limit(10)
            ->get();

        $topCompetition = Competition::where('status', 'active')->first();
        $standings = collect();

        if ($topCompetition) {
            $standings = Standing::with('team')
                ->where('competition_id', $topCompetition->id)
                ->orderBy('position')
                ->get();
        }

        $lineupReminders = collect();
        $teamIds = $user->isTeamManager() ? $user->managedTeamIds() : [];
        if (!empty($teamIds)) {
            $upcomingTeamMatches = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now())
                ->where(function ($q) use ($teamIds) {
                    $q->whereIn('home_team_id', $teamIds)
                       ->orWhereIn('away_team_id', $teamIds);
                })
                ->orderBy('match_date')
                ->limit(10)
                ->get();

            foreach ($upcomingTeamMatches as $match) {
                $matchTeamIds = array_intersect($teamIds, [$match->home_team_id, $match->away_team_id]);
                foreach ($matchTeamIds as $matchTeamId) {
                    $submission = LineupSubmission::where('match_game_id', $match->id)
                        ->where('team_id', $matchTeamId)
                        ->first();

                    $deadline = $match->match_date ? $match->match_date->copy()->subHour() : null;
                    $isOverdue = $deadline && now()->isAfter($deadline);

                    $lineupReminders->push([
                        'match' => $match,
                        'submission' => $submission,
                        'deadline' => $deadline,
                        'isOverdue' => $isOverdue,
                        'team_id' => $matchTeamId,
                    ]);
                }
            }
        }

        $jerseyReminders = collect();
        if (!empty($teamIds)) {
            $jUpcoming = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now())
                ->where(function ($q) use ($teamIds) {
                    $q->whereIn('home_team_id', $teamIds)
                       ->orWhereIn('away_team_id', $teamIds);
                })
                ->orderBy('match_date')
                ->limit(10)
                ->get();

            foreach ($jUpcoming as $match) {
                $matchTeamIds = array_intersect($teamIds, [$match->home_team_id, $match->away_team_id]);
                foreach ($matchTeamIds as $matchTeamId) {
                    $jersey = MatchJersey::where('match_game_id', $match->id)
                        ->where('team_id', $matchTeamId)
                        ->first();

                    // Only remind when not yet submitted (draft or missing)
                    if ($jersey && !in_array($jersey->status, ['draft'])) {
                        continue;
                    }

                    $deadline = $match->match_date ? $match->match_date->copy()->subDays(3) : null;
                    $jerseyReminders->push([
                        'match' => $match,
                        'deadline' => $deadline,
                        'isOverdue' => $deadline && now()->isAfter($deadline),
                        'team_id' => $matchTeamId,
                    ]);
                }
            }
        }

        // Recently-confirmed payments to show the team manager a "Payment Confirmed" banner.
        $paymentConfirmations = collect();
        if (!empty($teamIds)) {
            $paymentConfirmations = RegistrationPayment::with(['team', 'competition'])
                ->whereIn('team_id', $teamIds)
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->where('paid_at', '>=', now()->subDays(14))
                ->orderByDesc('paid_at')
                ->get();
        }

        $pendingReviews = collect();
        if ($user->isSuper() || $user->isLeagueAdmin()) {
            $pendingReviews = LineupSubmission::with(['matchGame.homeTeam', 'matchGame.awayTeam', 'matchGame.competition', 'team'])
                ->where('status', 'submitted')
                ->orderBy('created_at')
                ->limit(10)
                ->get();
        }

        // Match Commissioner (and Head MC who assigned himself): matches they have
        // been assigned to (dashboard notification).
        $mcAssignments = collect();
        if ($user->isMatchCommissioner() || $user->isHeadMatchCommissioner()) {
            $mcAssignments = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
                ->where('assigned_mc_user_id', $user->id)
                ->where('status', '!=', 'closed')
                ->orderBy('match_date')
                ->get();
        }

        return view('dashboard', compact(
            'competitionCount',
            'teamCount',
            'playerCount',
            'upcomingMatchCount',
            'recentMatches',
            'standings',
            'topCompetition',
            'lineupReminders',
            'jerseyReminders',
            'paymentConfirmations',
            'pendingReviews',
            'mcAssignments'
        ));
    }
}
