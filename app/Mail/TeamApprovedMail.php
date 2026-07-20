<?php

namespace App\Mail;

use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Team $team;
    public string $competitionNameMalay;

    public function __construct(Team $team)
    {
        $this->team = $team;
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
            subject: 'Surat Kelayakan / Letter of Eligibility - ' . $this->team->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-approved',
        );
    }

    public function attachments(): array
    {
        if ($this->team->competition_id == 1) {
            return [];
        }

        $team = $this->team->load(['competition', 'group', 'statusLogs']);

        $jbfaLogoBase64 = null;
        $jbfaLogoPath = public_path('images/jbfa_logo.png');
        if (file_exists($jbfaLogoPath)) {
            $jbfaLogoBase64 = 'data:' . mime_content_type($jbfaLogoPath) . ';base64,' . base64_encode(file_get_contents($jbfaLogoPath));
        }

        $competitionLogoBase64 = null;
        if ($team->competition && $team->competition->logo) {
            $logoPath = storage_path('app/public/' . $team->competition->logo);
            if (file_exists($logoPath)) {
                $competitionLogoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = Pdf::loadView('pdf.eligibility-letter', compact(
            'team',
            'jbfaLogoBase64',
            'competitionLogoBase64',
        ));

        $pdf->setPaper('a4', 'portrait');

        $refNo = 'JBFA-EL-' . str_pad($team->id, 6, '0', STR_PAD_LEFT);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                'eligibility-letter-' . $refNo . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
