<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Forms\Submission;
use Statamic\Forms\Connections\ConnectionLogic;
use Statamic\Sites\Site;

class SendEmails implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(protected Submission $submission, protected Site $site, protected ?array $config = null)
    {
    }

    public function handle(): void
    {
        $class = config('statamic.forms.send_email_job');
        $submission = $this->submission->form()->submission($this->submission->id()) ?? $this->submission;

        // Falls back to reading the form's own connections for anyone dispatching this
        // job directly without the $config argument, using the dispatched submission's
        // form instance so in-memory changes are respected. Remove the fallback in v7.
        $emailConfigs = $this->config ?? $this->submission->form()->connections()->get('email', []);

        $this->prependToChain(
            collect($emailConfigs)
                ->filter(fn (array $config) => ConnectionLogic::passes($config, $submission))
                ->map(fn (array $config) => new $class($submission, $this->site, $config))
                ->values()
                ->all()
        );
    }
}
