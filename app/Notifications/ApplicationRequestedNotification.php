<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Bewerbungsanfrage von '.$this->application->company_name)
            ->greeting('Hallo!')
            ->line('Die Firma **'.$this->application->company_name.'** hat Ihre Bewerbung angefragt.')
            ->line('Bitte senden Sie der Firma einen individuellen Bewerbungslink an '.$this->application->contact_email.'.')
            ->action('Bewerbungslink senden', route('applications.show', $this->application));

        if (filled($this->application->contact_email)) {
            $message->replyTo($this->application->contact_email);
        }

        return $message;
    }
}
