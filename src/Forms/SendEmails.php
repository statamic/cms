<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\Submission;
use Statamic\Forms\Connections\ConnectionLogic;
use Statamic\Sites\Site;

class SendEmails implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $submission;
    protected $site;

    public function __construct(Submission $submission, Site $site)
    {
        $this->submission = $submission;
        $this->site = $site;
    }

    public function handle(): void
    {
        $submission = $this->submission->form()->submission($this->submission->id()) ?? $this->submission;

        $this->emailConfigs($submission)
            ->map(function ($config) use ($submission) {
                $class = config('statamic.forms.send_email_job');

                return new $class($submission, $this->site, $config);
            })
            ->each(fn ($job) => $this->prependtoChain($job));
    }

    private function emailConfigs($submission): Collection
    {
        return collect($submission->form()->connections()->get('email', []))
            ->reject(fn (array $config) => ($config['enabled'] ?? true) === false)
            ->filter(fn (array $config) => ConnectionLogic::passes($config, $submission));
    }
}
