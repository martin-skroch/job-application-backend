<?php

namespace App\Notifications;

use App\Models\Vacancy;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacancyRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Vacancy $vacancy
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Allgemeine Bewerbungsanfrage von '.$this->vacancy->company_name)
            ->greeting('Hallo!')
            ->line('**'.$this->vacancy->company_name.'** hat allgemein eine Bewerbung angefragt.')
            ->line('Es wurde eine neue Stelle angelegt. Bitte erstelle daraus eine Bewerbung und sende einen individuellen Bewerbungslink an **'.$this->vacancy->contact_email.'**.')
            ->action('Stelle ansehen', route('vacancies.show', $this->vacancy));

        if (filled($this->vacancy->contact_email)) {
            $message->replyTo($this->vacancy->contact_email);
        }

        return $message;
    }
}
