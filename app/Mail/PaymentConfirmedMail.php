<?php

namespace App\Mail;

use App\Models\RegistrationPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public RegistrationPayment $payment;

    public function __construct(RegistrationPayment $payment)
    {
        $this->payment = $payment;
    }

    public function envelope(): Envelope
    {
        $teamName = $this->payment->team->name ?? 'Your team';

        return new Envelope(
            subject: 'Payment Confirmed / Pembayaran Disahkan - ' . $teamName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmed',
        );
    }
}
