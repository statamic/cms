<?php

namespace Statamic\Forms\Listeners;

use Statamic\Forms\Email;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Forms\Submission;

class SendEmails
{
    public function handle(Submission $submission)
    {
        $config = $submission->formset()->get('email', []);

        // Ensure its an array of emails
        $config = (isset($config['to'])) ? [$config] : $config;

        foreach ($config as $c) {
            Mail::send(new Email($submission, $c));
        }
    }
}
