<?php

namespace Statamic\Forms\Listeners;

use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Forms\Submission;
use Statamic\Forms\Email;

class SendEmails
{
    public function handle(Submission $submission)
    {
        $config = $submission->form()->email() ?? [];

        // Ensure its an array of emails
        $config = (isset($config['to'])) ? [$config] : $config;

        foreach ($config as $c) {
            Mail::send(new Email($submission, $c));
        }
    }
}
