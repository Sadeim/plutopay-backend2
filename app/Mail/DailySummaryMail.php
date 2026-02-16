<?php

namespace App\Mail;

use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Merchant $merchant,
        public array $summary,
        public string $date
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "PlutoPay Daily Summary - {$this->date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-summary',
        );
    }
}
