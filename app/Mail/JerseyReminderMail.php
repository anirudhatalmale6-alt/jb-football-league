<?php

namespace App\Mail;

use App\Models\MatchGame;
use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JerseyReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public MatchGame $match;
    public Team $team;
    public int $daysBefore;
    public string $deadline;

    public function __construct(MatchGame $match, Team $team, int $daysBefore)
    {
        $this->match = $match;
        $this->team = $team;
        $this->daysBefore = $daysBefore;
        $this->deadline = $match->match_date
            ? $match->match_date->copy()->subDays(3)->format('d M Y, h:i A')
            : 'TBC';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jersey Colour Submission Reminder - ' . $this->team->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.jersey-reminder',
        );
    }
}
