<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Antlers;

class SendEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $submission;

    protected $locale;

    public function __construct(Submission $submission, $locale)
    {
        $this->submission = $submission;
        $this->locale = $locale;
    }

    /**
     * Send form submission emails.
     *
     * @param Submission $submission
     */
    public function handle()
    {
        $this->parseEmailConfigs($this->submission)->each(function ($config) {
            Mail::send((new Email($this->submission, $config))->locale($this->locale));
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
