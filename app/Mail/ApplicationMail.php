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
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if (filled($this->application->profile?->email)) {
            $fromAddress = $this->application->profile->email;
        }

        if (filled($this->application->profile?->name)) {
            $fromName = $this->application->profile->name;
        }

        // From
        $from = new Address($fromAddress, $fromName);

        // To
        if ($this->isTest) {
            $to = new Address($this->application->profile->email);
        } else {
            $to = new Address($this->application->contact_email);
        }

        // Subject
        $subject = __('mail.application.subject', ['title' => $this->application->title]);

        if ($this->isTest) {
            $subject = __('mail.application.subject_test', ['subject' => $subject]);
        }

        return new Envelope(
            from: $from,
            to: [$to],
            bcc: [$from],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.application'
        );
    }

    public function renderHtml(): string
    {
        return app(Markdown::class)->render(
            view: 'mail.application',
            data: $this->buildViewData()
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
