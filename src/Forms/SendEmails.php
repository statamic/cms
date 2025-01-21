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
            ->when($this->shouldDeleteTemporaryAttachments(), function ($jobs) {
                $jobs->push(new DeleteTemporaryAttachments($this->submission));
            });
    }

    private function emailConfigs($submission)
    {
        $config = $submission->form()->email();

        $config = isset($config['to']) ? [$config] : $config;

        return collect($config);
    }

    protected function shouldDeleteTemporaryAttachments(): bool
    {
        return $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->fieldtype()->handle() === 'files')
            ->filter()
            ->count() > 0;
    }
}
