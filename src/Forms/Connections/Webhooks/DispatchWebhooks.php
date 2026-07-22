<?php

namespace Statamic\Forms\Connections\Webhooks;

use Statamic\Events\SubmissionFinalized;
use Statamic\Forms\Logic\RuleEvaluator;

class DispatchWebhooks
{
    public function handle(SubmissionFinalized $event)
    {
        $submission = $event->submission;

        collect($submission->form()->connections()->get('webhook', []))
            ->reject(fn (array $config) => ($config['enabled'] ?? true) === false)
            ->filter(function (array $config) use ($submission) {
                if (empty($config['conditions'])) {
                    return true;
                }

                return (new RuleEvaluator)->passes($config['conditions'], $submission->toArray());
            })
            ->each(fn (array $config) => SendWebhook::dispatch($submission, $submission->site(), $config));
    }
}
