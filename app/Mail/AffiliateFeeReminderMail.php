<?php

namespace App\Mail;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateFeeReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Team $team;
    public float $amount;

    public function __construct(Team $team)
    {
        $this->team = $team;
        $this->amount = Team::AFFILIATE_FEE;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Peringatan Yuran Keahlian Gabungan / Affiliate Membership Fee Reminder - ' . $this->team->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-reminder',
        );
    }
}
