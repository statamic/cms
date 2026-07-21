<?php

namespace Statamic\Forms\Connections\Webhooks;

use Statamic\Events\SubmissionFinalized;

class DispatchWebhooks
{
    public function handle(SubmissionFinalized $event)
    {
        $submission = $event->submission;

        collect($submission->form()->connections()->get('webhook', []))
            ->reject(fn ($config) => ($config['enabled'] ?? true) === false)
            ->each(fn ($config) => SendWebhook::dispatch($submission, $submission->site(), $config));
    }
}
