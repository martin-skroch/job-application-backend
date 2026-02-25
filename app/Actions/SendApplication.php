<?php

namespace App\Actions;

use App\Enum\ApplicationStatus;
use App\Mail\ApplicationMail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

class SendApplication
{
    public function __construct(private PublishApplication $publishApplication) {}

    public function handle(Application $application, bool $setStatus = true): void
    {
        $this->publishApplication->handle($application);

        $mail = new ApplicationMail($application, isTest: ! $setStatus);

        Mail::send($mail);

        if ($setStatus) {
            $application->history()->create([
                'status' => ApplicationStatus::Sent,
                'comment' => $mail->renderText(),
            ]);
        }
    }
}
