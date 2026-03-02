<?php

return [

    'application' => [
        'subject' => 'Bewerbung als :title',
        'subject_test' => '[Test] :subject',
        'button' => 'Bewerbung ansehen',
        'formal' => [
            'salutation_named' => 'Hallo :name,',
            'salutation_generic' => 'Hallo,',
            'body' => "ich bin Martin Skroch, erfahrener Webentwickler – und bewerbe mich hiermit für die Position als **:application_title**.\n\nMeine Bewerbung finden Sie unter folgendem Link:",
            'hearing_from_you' => 'Ich freue mich, von Ihnen zu hören.',
            'closing' => 'Mit freundlichen Grüßen',
        ],
        'informal' => [
            'salutation_named' => 'Hallo :name,',
            'salutation_generic' => 'Hallo,',
            'body' => "ich bin Martin Skroch, erfahrener Webentwickler – und bewerbe mich hiermit für die Position als **:application_title**.\n\nMeine Bewerbung findest du unter folgendem Link:",
            'hearing_from_you' => 'Ich freue mich, von dir zu hören.',
            'closing' => 'Viele Grüße',
        ],
    ],

];
