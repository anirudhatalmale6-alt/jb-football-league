<?php

namespace App\Console\Commands;

use App\Mail\JerseyReminderMail;
use App\Models\MatchGame;
use App\Models\MatchJersey;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendJerseyReminders extends Command
{
    protected $signature = 'jerseys:send-reminders';

    protected $description = 'Email team managers who have not submitted jersey colours 5 and 3 days before their match';

    public function handle(): int
    {
        // Reminders fire when a match is exactly 5 or 3 days away.
        $windows = [5, 3];
        $sent = 0;

        foreach ($windows as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $matches = MatchGame::with(['homeTeam', 'awayTeam', 'competition'])
                ->where('status', 'scheduled')
                ->whereDate('match_date', $targetDate)
                ->get();

            foreach ($matches as $match) {
                foreach ([$match->home_team_id, $match->away_team_id] as $teamId) {
                    $jersey = MatchJersey::where('match_game_id', $match->id)
                        ->where('team_id', $teamId)
                        ->first();

                    // Skip if already submitted (anything beyond draft)
                    if ($jersey && $jersey->status !== 'draft') {
                        continue;
                    }

                    $team = Team::find($teamId);
                    if (!$team) {
                        continue;
                    }

                    $recipients = $this->recipientsFor($team);
                    if (empty($recipients)) {
                        continue;
                    }

                    try {
                        Mail::to($recipients)->send(new JerseyReminderMail($match, $team, $days));
                        $sent++;
                        $this->info("Reminder sent: {$team->name} (match #{$match->id}, {$days}d) -> " . implode(', ', $recipients));
                    } catch (\Throwable $e) {
                        $this->error("Failed for {$team->name} (match #{$match->id}): " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Done. {$sent} reminder(s) sent.");
        return self::SUCCESS;
    }

    private function recipientsFor(Team $team): array
    {
        $emails = [];

        // Managers linked via the team_user pivot
        $managerEmails = User::whereHas('managedTeams', function ($q) use ($team) {
            $q->where('teams.id', $team->id);
        })->pluck('email')->filter()->toArray();

        $emails = array_merge($emails, $managerEmails);

        // Team contact email as a fallback / additional recipient
        if ($team->contact_email) {
            $emails[] = $team->contact_email;
        }

        return array_values(array_unique(array_filter($emails)));
    }
}
