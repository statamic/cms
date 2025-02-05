<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Antlers;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\Form;
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
    private $globalData;

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
        $this->to($this->addresses(Arr::get($this->config, 'to')));

        if ($from = Arr::get($this->config, 'from')) {
            $this->from($this->addresses($from));
        }

        if ($replyTo = Arr::get($this->config, 'reply_to')) {
            $this->replyTo($this->addresses($replyTo));
        }

        if ($cc = Arr::get($this->config, 'cc')) {
            $this->cc($this->addresses($cc));
        }

        if ($bcc = Arr::get($this->config, 'bcc')) {
            $this->bcc($this->addresses($bcc));
        }

        return $this;
    }

    protected function addViews()
    {
        $html = Arr::get($this->config, 'html');
        $text = Arr::get($this->config, 'text');

        if (! $text && ! $html) {
            return $this->view('statamic::forms.automagic-email');
        }

        if ($text) {
            $this->text($text);
        }

        if ($html) {
            $method = Arr::get($this->config, 'markdown') ? 'markdown' : 'view';
            $this->$method($html);
        }

        return $this;
    }

    protected function addAttachments()
    {
        if (! Arr::get($this->config, 'attachments')) {
            return $this;
        }

        $this->getRenderableFieldData(Arr::except($this->submissionData, ['id', 'date', 'form']))
            ->filter(fn ($field) => in_array($field['fieldtype'], ['assets', 'files']))
            ->each(function ($field) {
                $field['value'] = $field['value']->value();
                $field['fieldtype'] === 'assets' ? $this->attachAssets($field) : $this->attachFiles($field);
            });

        return $this;
    }

    private function attachAssets($field)
    {
        $value = $field['value'];

        $value = Arr::get($field, 'config.max_files') === 1
            ? collect([$value])->filter()
            : $value->get();

        foreach ($value as $asset) {
            $this->attachFromStorageDisk($asset->container()->diskHandle(), $asset->path());
        }
    }

    private function attachFiles($field)
    {
        $value = $field['value'];

        $value = Arr::get($field, 'config.max_files') === 1
            ? collect([$value])->filter()
            : $value;

        if (! $value) {
            return;
        }

        foreach ($value as $file) {
            $this->attachFromStorageDisk('local', 'statamic/file-uploads/'.$file);
        }
    }

    protected function addData()
    {
        $augmented = $this->submission->toAugmentedArray();
        $form = $this->submission->form();
        $fields = $this->getRenderableFieldData(Arr::except($augmented, ['id', 'date', 'form']))
            ->reject(fn ($field) => $field['fieldtype'] === 'spacer')
            ->when(Arr::has($this->config, 'attachments'), function ($fields) {
                return $fields->reject(fn ($field) => in_array($field['fieldtype'], ['assets', 'files']));
            });
        $form_config = ($configFields = Form::extraConfigFor($form->handle()))
            ? Blueprint::makeFromTabs($configFields)->fields()->addValues($form->data()->all())->values()->all()
            : [];

        $data = array_merge($augmented, $this->getGlobalsData(), [
            'form_config' => $form_config,
            'email_config' => $this->config,
            'config' => config()->all(),
            'fields' => $fields,
            'site_url' => Config::getSiteUrl(),
            'date' => now(),
            'now' => now(),
            'today' => now(),
            'site' => $this->site->handle(),
            'locale' => $this->site->handle(),
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
        if (! is_null($this->globalData)) {
            return $this->globalData;
        }

        $data = [];

        foreach (GlobalSet::all() as $global) {
            if (! $global->existsIn($this->site->handle())) {
                continue;
            }

            $global = $global->in($this->site->handle());

            $data[$global->handle()] = $global->toAugmentedArray();
        }

        return $this->globalData = array_merge($data, $data['global'] ?? []);
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

            return (string) Antlers::parse($value, array_merge(
                ['config' => config()->all()],
                $this->getGlobalsData(),
                $this->submissionData,
            ));
        });
    }
}
