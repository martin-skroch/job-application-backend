<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Markdown;
use Illuminate\Queue\SerializesModels;

class ApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public bool $isTest = false,
    ) {}

    public function envelope(): Envelope
    {
        // From
        $from = null;

        if (filled($this->application->profile?->email)) {
            $from = new Address(
                $this->application->profile->email,
                $this->application->profile->name ?? null
            );
        }

        // To
        $to = new Address($this->application->contact_email);

        if ($this->isTest) {
            $to = new Address($this->application->profile->email);
        }

        // Subject
        $subject = __('mail.application.subject', ['title' => $this->application->title]);

        if ($this->isTest) {
            $subject = __('mail.application.subject_test', ['subject' => $subject]);
        }

        return new Envelope(
            from: $from,
            to: [$to],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.application',
            text: 'mail.application'
        );
    }

    public function renderText(): string
    {
        return app(Markdown::class)->renderText(
            view: 'mail.application',
            data: $this->buildViewData()
        );
    }
}
