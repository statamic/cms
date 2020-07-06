<?php

namespace Statamic\Forms\Listeners;

use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Antlers;
use Statamic\Forms\Email;

class SendEmails
{
    /**
     * Send form submission emails.
     *
     * @param Submission $submission
     */
    public function handle(Submission $submission)
    {
        $this->parseEmailConfigs($submission)->each(function ($config) use ($submission) {
            Mail::send(new Email($submission, $config));
        });
    }

    /**
     * Parse email configs.
     *
     * @param \Statamic\Forms\Submission $submission
     * @return \Illuminate\Support\Collection
     */
    protected function parseEmailConfigs($submission)
    {
        $config = $submission->form()->email();

        if (! $config) {
            return collect();
        }

        $config = isset($config['to']) ? [$config] : $config;

        return collect($config)->map(function ($config) use ($submission) {
            return $this->parseAntlersInConfig($config, $submission->data());
        });
    }

    /**
     * Parse antlers in email configs.
     *
     * @param array $config
     * @param array $data
     * @return array
     */
    protected function parseAntlersInConfig($config, $data)
    {
        return collect($config)
            ->map(function ($value) use ($data) {
                return Antlers::parse($value, collect($data)->filter());
            })
            ->all();
    }
}
