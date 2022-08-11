<?php

namespace Statamic\Forms;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Statamic\Contracts\Forms\Submission;
use Statamic\Sites\Site;

class SendEmails
{
    use Dispatchable, SerializesModels;

    protected $submission;
    protected $site;

    public function __construct(Submission $submission, Site $site)
    {
        $this->submission = $submission;
        $this->site = $site;
    }

    public function handle()
    {
        $this->jobs()->each(fn ($job) => Bus::dispatch($job));
    }

    private function jobs()
    {
        return $this->emailConfigs($this->submission)->map(function ($config) {
            $class = config('statamic.forms.send_email_job');

            return new $class($this->submission, $this->site, $config);
        });
    }

    private function emailConfigs($submission)
    {
        $config = $submission->form()->email();

        $config = isset($config['to']) ? [$config] : $config;

        return collect($config);
    }
}
