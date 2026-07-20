<?php

namespace App\Mail;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Team $team;
    public string $competitionNameMalay;
    public string $rejectionReason;

    public function __construct(Team $team)
    {
        $this->team = $team;
        $this->rejectionReason = $team->rejection_reason ?? 'Tiada sebab dinyatakan.';
        $this->competitionNameMalay = match($team->competition_id) {
            2 => 'LIGA SUPER JBFA',
            3 => 'LIGA PERDANA JBFA',
            4 => 'LIGA DIVISYEN JBFA',
            5 => 'PIALA FA JBFA',
            default => $team->competition->name ?? 'JBFA Football League',
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Penyertaan Ditolak / Registration Rejected - ' . $this->team->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
