<?php

namespace Statamic\Forms\Connections;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\VueComponent;

abstract class Connection
{
    use HasHandle, HasTitle, RegistersItself {
        handle as protected traitHandle;
    }

    protected $description;
    protected $icon;
    protected $smallIcon;
    protected $developer;
    protected $config = [];

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_connection');
    }

    public function setConfig(array $config): static
    {
        $this->config = static::normalizeRows($config);

        return $this;
    }

    public function config(): array
    {
        return $this->config;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function icon(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        return Str::startsWith($this->icon, '<svg') ? $this->icon : Statamic::svg('icons/'.$this->icon);
    }

    public function smallIcon(): ?string
    {
        if (! $this->smallIcon) {
            return $this->icon();
        }

        return Str::startsWith($this->smallIcon, '<svg') ? $this->smallIcon : Statamic::svg('icons/'.$this->smallIcon);
    }

    public function developer(): ?string
    {
        return $this->developer;
    }

    public static function normalizeRows(mixed $config): array
    {
        return collect(is_array($config) && array_is_list($config) ? $config : [$config])
            ->filter(fn ($row) => is_array($row))
            ->values()
            ->all();
    }

    public function count(Form $form): ?int
    {
        return count($form->connections()->get(static::handle(), []));
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function finalized(Submission $submission): object|array
    {
        return collect($this->config())
            ->filter(fn (array $row) => ConnectionLogic::passes($row, $submission))
            ->map(fn (array $row) => $this->job($submission, $row))
            ->filter()
            ->values()
            ->all();
    }

    protected function job(Submission $submission, array $row): ?object
    {
        return null;
    }

    abstract public function render(Form $form): VueComponent;

    public function preProcess(array $config, Form $form): array
    {
        return collect(static::normalizeRows($config))
            ->map(fn (array $row): array => [
                ...$this->preProcessRow($row, $form),
                'id' => Arr::get($row, 'id') ?? Str::random(8),
                'enabled' => Arr::get($row, 'enabled') !== false,
                'conditions' => ConnectionLogic::preProcess(Arr::get($row, 'conditions') ?? []),
            ])
            ->all();
    }

    protected function preProcessRow(array $row, Form $form): array
    {
        return $row;
    }

    public function rules(Form $form): array
    {
        return [
            '*' => ['array'],
            ...collect($this->rowRules($form))->mapWithKeys(fn ($rules, $key) => ['*.'.$key => $rules])->all(),
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ];
    }

    protected function rowRules(Form $form): array
    {
        return [];
    }

    public function process(array $config, Form $form): array
    {
        return collect(static::normalizeRows($config))
            ->map(function (array $row) use ($form): array {
                $row = Arr::removeNullValues($row);

                return Arr::removeNullValues([
                    'id' => Arr::get($row, 'id') ?? Str::random(8),
                    ...$this->processRow(Arr::except($row, ['id', 'enabled', 'conditions']), $form),
                    'enabled' => Arr::get($row, 'enabled') === false ? false : null,
                    'conditions' => ConnectionLogic::process(Arr::get($row, 'conditions') ?? []),
                ]);
            })
            ->all();
    }

    protected function processRow(array $row, Form $form): array
    {
        return $row;
    }

    public function routes(Router $router): void
    {
        //
    }
}
