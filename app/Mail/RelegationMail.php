<?php

namespace App\Mail;

use App\Models\PromotionOffer;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RelegationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Team $team;
    public PromotionOffer $offer;

    public function __construct(Team $team, PromotionOffer $offer)
    {
        $this->team = $team;
        $this->offer = $offer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Penurunan Pangkat ke Liga Perdana / Relegation to Premier League - ' . $this->team->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.relegation-notice',
        );
    }

    public function attachments(): array
    {
        $team = $this->team;
        $offer = $this->offer;

        $jbfaLogoBase64 = null;
        $jbfaLogoPath = public_path('images/jbfa_logo.png');
        if (file_exists($jbfaLogoPath)) {
            $jbfaLogoBase64 = 'data:' . mime_content_type($jbfaLogoPath) . ';base64,' . base64_encode(file_get_contents($jbfaLogoPath));
        }

        $pdf = Pdf::loadView('pdf.relegation-letter', compact('team', 'offer', 'jbfaLogoBase64'));
        $pdf->setPaper('a4', 'portrait');

        $refNo = 'JBFA-RL-' . str_pad($offer->id, 6, '0', STR_PAD_LEFT);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                'relegation-letter-' . $refNo . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
