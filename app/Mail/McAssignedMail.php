<?php

namespace App\Mail;

use App\Models\MatchGame;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class McAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public MatchGame $match;
    public User $commissioner;

    public function __construct(MatchGame $match, User $commissioner)
    {
        $this->match = $match;
        $this->commissioner = $commissioner;
    }

    public function envelope(): Envelope
    {
        $home = $this->match->homeTeam->name ?? 'Home';
        $away = $this->match->awayTeam->name ?? 'Away';

        return new Envelope(
            subject: 'Match Commissioner Assignment / Tugasan Pesuruhjaya - ' . $home . ' vs ' . $away,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mc-assigned',
        );
    }
}
