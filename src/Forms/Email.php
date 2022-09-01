<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Antlers;
use Statamic\Facades\Config;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Parse;
use Statamic\Sites\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    protected $submission;
    protected $submissionData;
    protected $config;
    protected $site;

    public function __construct(Submission $submission, array $config, Site $site)
    {
        $this->submission = $submission;
        $this->config = $config;
        $this->site = $site;
        $this->locale($site->lang());
    }

    public function getSubmission()
    {
        return $this->submission;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function build()
    {
        $this->submissionData = $this->submission->toAugmentedArray();
        $this->config = $this->parseConfig($this->config);

        $this
            ->subject(isset($this->config['subject']) ? __($this->config['subject']) : __('Form Submission'))
            ->addAddresses()
            ->addViews()
            ->addAttachments()
            ->addData();
    }

    protected function addAddresses()
    {
        $this->to($this->addresses(array_get($this->config, 'to')));

        if ($from = array_get($this->config, 'from')) {
            $this->from($this->addresses($from));
        }

        if ($replyTo = array_get($this->config, 'reply_to')) {
            $this->replyTo($this->addresses($replyTo));
        }

        if ($cc = array_get($this->config, 'cc')) {
            $this->cc($this->addresses($cc));
        }

        if ($bcc = array_get($this->config, 'bcc')) {
            $this->bcc($this->addresses($bcc));
        }

        return $this;
    }

    protected function addViews()
    {
        $html = array_get($this->config, 'html');
        $text = array_get($this->config, 'text');

        if (! $text && ! $html) {
            return $this->view('statamic::forms.automagic-email');
        }

        if ($text) {
            $this->text($text);
        }

        if ($html) {
            $method = array_get($this->config, 'markdown') ? 'markdown' : 'view';
            $this->$method($html);
        }

        return $this;
    }

    protected function addAttachments()
    {
        if (! array_get($this->config, 'attachments')) {
            return $this;
        }

        $this->getRenderableFieldData(Arr::except($this->submissionData, ['id', 'date', 'form']))
            ->filter(function ($field) {
                return $field['fieldtype'] === 'assets';
            })
            ->each(function ($field) {
                $value = $field['value']->value();

                $value = array_get($field, 'config.max_files') === 1
                    ? collect([$value])->filter()
                    : $value->get();

                foreach ($value as $file) {
                    $this->attachFromStorageDisk($file->container()->diskHandle(), $file->path());
                }
            });

        return $this;
    }

    protected function addData()
    {
        $augmented = $this->submission->toAugmentedArray();

        $data = array_merge($augmented, $this->getGlobalsData(), [
            'config'     => config()->all(),
            'fields'     => $this->getRenderableFieldData(Arr::except($augmented, ['id', 'date', 'form'])),
            'site_url'   => Config::getSiteUrl(),
            'date'       => now(),
            'now'        => now(),
            'today'      => now(),
            'site'       => $this->site->handle(),
            'locale'     => $this->site->handle(),
        ]);

        return $this->with($data);
    }

    protected function getRenderableFieldData($values)
    {
        return collect($values)->map(function ($value, $handle) {
            $field = $value->field();
            $display = $field->display();
            $fieldtype = $field->type();
            $config = $field->config();

            return compact('display', 'handle', 'fieldtype', 'config', 'value');
        })->values();
    }

    private function getGlobalsData()
    {
        $data = [];

        foreach (GlobalSet::all() as $global) {
            if (! $global->existsIn($this->site->handle())) {
                continue;
            }

            $global = $global->in($this->site->handle());

            $data[$global->handle()] = $global->toAugmentedArray();
        }

        return array_merge($data, $data['global'] ?? []);
    }

    protected function addresses($addresses)
    {
        if (! $addresses) {
            return;
        }

        return collect(explode(',', $addresses))->map(function ($address) {
            $name = null;
            $email = trim($address);

            if (Str::contains($email, '<')) {
                preg_match('/^(.*) \<(.*)\>$/', $email, $matches);
                $name = $matches[1];
                $email = $matches[2];
            }

            return [
                'email' => $email,
                'name' => $name,
            ];
        })->all();
    }

    protected function parseConfig(array $config)
    {
        return collect($config)->map(function ($value) {
            $value = Parse::env($value); // deprecated

            return (string) Antlers::parse($value, $this->submissionData);
        });
    }
}
