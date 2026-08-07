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
        $class = config('statamic.forms.send_email_job');
        $submission = $this->submission->form()->submission($this->submission->id()) ?? $this->submission;

        $this->prependToChain(
            $this->emailConfigs($submission)
                ->map(fn (array $config) => new $class($submission, $this->site, $config))
                ->all()
        );
    }

    private function emailConfigs(Submission $submission): Collection
    {
        return collect($submission->form()->connections()->get('email', []))
            ->reject(fn (array $config) => ($config['enabled'] ?? true) === false)
            ->filter(fn (array $config) => ConnectionLogic::passes($config, $submission));
    }
}
