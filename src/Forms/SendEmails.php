<?php

namespace Statamic\Forms;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Statamic\Contracts\Forms\Submission;
use Statamic\Fields\Field;
use Statamic\Forms\Logic\RuleEvaluator;
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
        return collect($submission->form()->connections()->get('email', []))
            ->reject(fn ($config) => ($config['enabled'] ?? true) === false)
            ->filter(fn ($config) => empty($config['conditions'])
                || (new RuleEvaluator)->passes($config['conditions'], $submission->toArray()));
    }

    protected function shouldDeleteTemporaryAttachments(): bool
    {
        return $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->fieldtype()->handle() === 'files')
            ->filter()
            ->count() > 0;
    }
}
