<?php

namespace Statamic\Forms;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Statamic\Contracts\Forms\Submission;
use Statamic\Fields\Field;
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

    public function handle(): void
    {
        $jobs = $this->jobs();

        if ($jobs->isNotEmpty()) {
            Bus::chain($jobs)->dispatch();
        }
    }

    private function jobs(): Collection
    {
        return $this->emailConfigs($this->submission)
            ->map(function ($config) {
                $class = config('statamic.forms.send_email_job');

                return new $class($this->submission, $this->site, $config);
            })
            ->when($this->shouldDeleteTemporaryFiles(), function ($jobs) {
                $jobs->push(new DeleteTemporaryFiles($this->submission));
            });
    }

    private function emailConfigs($submission)
    {
        $config = $submission->form()->email();

        $config = isset($config['to']) ? [$config] : $config;

        return collect($config);
    }

    private function shouldDeleteTemporaryFiles(): bool
    {
        return $this->submission->form()->blueprint()->fields()->all()
            ->contains(fn (Field $field) => in_array($field->type(), ['files', 'form_upload']));
    }
}
