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
use Statamic\View\Cascade;

use function Statamic\trans as __;

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
            ->filter(fn ($field) => in_array($field['fieldtype'], ['assets', 'files', 'form_upload']))
            ->each(function ($field) {
                $field['value'] = $field['value']->value();

                $isStoredAsAsset = $field['fieldtype'] === 'assets'
                    || ($field['fieldtype'] === 'form_upload' && Arr::get($field, 'config.store'));

                $isStoredAsAsset ? $this->attachAssets($field) : $this->attachFiles($field);
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

        $disk = config('statamic.system.file_uploads_disk', 'local');

        $basePath = $field['fieldtype'] === 'form_upload'
            ? config('statamic.forms.file_uploads_path', 'statamic/form-uploads')
            : config('statamic.system.file_uploads_path', 'statamic/file-uploads');

        foreach ($value as $file) {
            $this->attachFromStorageDisk($disk, $basePath.'/'.$file);
        }
    }

    protected function addData()
    {
        $augmented = $this->submission->toAugmentedArray();
        $form = $this->submission->form();
        $excludedFields = $form->formFields()->fields()
            ->filter(fn ($field) => array_intersect($field->fieldtype()->categories(), ['information', 'structure']))
            ->keys();
        $fields = $this->getRenderableFieldData(Arr::except($augmented, ['id', 'date', 'form']))
            ->reject(fn ($field) => $excludedFields->contains($field['handle']))
            ->when(Arr::has($this->config, 'attachments'), function ($fields) {
                return $fields->reject(fn ($field) => in_array($field['fieldtype'], ['assets', 'files', 'form_upload']));
            });
        $formConfig = ($configFields = Form::extraConfigFor($form->handle()))
            ? Blueprint::makeFromTabs($configFields)->fields()->addValues($form->data()->all())->values()->all()
            : [];

        $data = array_merge($augmented, $this->getGlobalsData(), [
            'form_config' => $formConfig,
            'email_config' => $this->config,
            'config' => Cascade::config(),
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

        return collect(Arr::wrap($addresses))
            ->flatMap(fn (string $address) => Str::startsWith($address, 'field:')
                ? Arr::wrap($this->submission->get(Str::after($address, 'field:')))
                : [$address])
            ->flatMap(fn (string $address) => explode(',', (string) $address))
            ->map(fn (string $address) => trim($this->sanitize($address)))
            ->filter()
            ->map(function (string $email): array {
                $name = null;

                if (Str::contains($email, '<') && preg_match('/^(.*) \<(.*)\>$/', $email, $matches)) {
                    $name = $matches[1];
                    $email = $matches[2];
                }

                return [
                    'email' => $email,
                    'name' => $name,
                ];
            })
            ->filter(fn (array $address) => filter_var($address['email'], FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();
    }

    private function sanitize($value)
    {
        return preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $value);
    }

    private function parseConfig(array $config)
    {
        return collect($config)
            ->except('conditions')
            ->map(fn ($value) => is_array($value)
                ? array_map(fn ($item) => Str::startsWith($item, 'field:') ? $item : $this->parseConfigValue($item), $value)
                : $this->parseConfigValue($value));
    }

    private function parseConfigValue($value)
    {
        $value = Parse::env($value); // deprecated

        $value = Parse::config($value);

        return (string) Antlers::parse($value, array_merge(
            ['config' => Cascade::config()],
            $this->getGlobalsData(),
            $this->submissionData,
        ));
    }
}
