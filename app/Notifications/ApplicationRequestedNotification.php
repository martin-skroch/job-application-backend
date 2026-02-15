<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Application;

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

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Bewerbungsanfrage von ' . $this->application->company_name)
            ->greeting('Hallo!')
            ->line('Die Firma **' . $this->application->company_name . '** hat Ihre Bewerbung angefragt.')
            ->line('Bitte senden Sie der Firma einen individuellen Bewerbungslink an ' . $this->application->contact_email . '.')
            ->action('Bewerbungslink senden', route('applications.show', $this->application));
    }
}
