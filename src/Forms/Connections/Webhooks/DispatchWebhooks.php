<?php

namespace Statamic\Forms\Connections\Webhooks;

use Statamic\Events\SubmissionFinalized;
use Statamic\Forms\Connections\ConnectionLogic;

class DispatchWebhooks
{
    public function handle(SubmissionFinalized $event)
    {
        $submission = $event->submission;

        collect($submission->form()->connections()->get('webhook', []))
            ->reject(fn (array $config) => ($config['enabled'] ?? true) === false)
            ->filter(fn (array $config) => ConnectionLogic::passes($config, $submission))
            ->each(fn (array $config) => SendWebhook::dispatch($submission, $submission->site(), $config));
    }
}
