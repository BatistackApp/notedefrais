<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class WeeklySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $stats,
        public Carbon $start,
        public Carbon $end
    ) {}

    public function envelope()
    {
        return new Envelope(
            subject: 'Synthèse Hebdomadaire des Dépenses',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.weekly-summary',
        );
    }
}
