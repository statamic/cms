<?php

namespace Statamic\Forms\Connectors;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Fields\Validator;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\VueComponent;

abstract class Connector
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
        return Str::removeRight(static::traitHandle(), '_connector');
    }

    public function setConfig(array $config): static
    {
        $this->config = static::normalizeConnections($config);

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

    public static function normalizeConnections(mixed $config): array
    {
        return collect(is_array($config) && array_is_list($config) ? $config : [$config])
            ->filter(fn ($connection) => is_array($connection))
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
            ->filter(fn (array $connection) => ConnectionLogic::passes($connection, $submission))
            ->map(fn (array $connection) => $this->job($submission, $connection))
            ->filter()
            ->values()
            ->all();
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        return null;
    }

    abstract public function render(Form $form): VueComponent;

    public function blueprint(Form $form): ?Blueprint
    {
        return null;
    }

    protected function connectionFields(array $connection, Form $form): Fields
    {
        return $this->blueprint($form)->fields()->addValues($connection)->preProcess();
    }

    protected function connectionMeta(array $connection, Form $form): array
    {
        return $this->connectionFields($connection, $form)->meta()->all();
    }

    protected function blueprintProps(Form $form, array $connections): array
    {
        $blueprint = $this->blueprint($form);
        $fields = $blueprint->fields()->preProcess();

        return [
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => collect(static::normalizeConnections($connections))
                ->filter(fn (array $connection): bool => isset($connection['id']))
                ->mapWithKeys(fn (array $connection): array => [
                    $connection['id'] => $this->connectionMeta($connection, $form),
                ])
                ->all(),
            'defaults' => [
                'values' => $fields->values()->all(),
                'meta' => $fields->meta()->all(),
            ],
        ];
    }

    public function preProcess(array $config, Form $form): array
    {
        return collect(static::normalizeConnections($config))
            ->map(fn (array $connection): array => [
                ...$this->preProcessConnection($connection, $form),
                'id' => Arr::get($connection, 'id') ?? Str::random(8),
                'enabled' => Arr::get($connection, 'enabled') !== false,
                'conditions' => ConnectionLogic::preProcess(Arr::get($connection, 'conditions') ?? []),
            ])
            ->all();
    }

    protected function preProcessConnection(array $connection, Form $form): array
    {
        if (! $this->blueprint($form)) {
            return $connection;
        }

        return $this->connectionFields($connection, $form)->values()->all();
    }

    public function rules(Form $form): array
    {
        return [
            '*' => ['array'],
            ...collect($this->blueprintRules($form))
                ->mergeRecursive(collect($this->connectionRules($form))->map(fn ($rules) => is_object($rules) ? [$rules] : Validator::explodeRules($rules)))
                ->mapWithKeys(fn ($rules, $key) => ['*.'.$key => $rules])
                ->all(),
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ];
    }

    protected function connectionRules(Form $form): array
    {
        return [];
    }

    // Only top-level field rules can be derived without values. Grid sub-field rules belong in connectionRules().
    private function blueprintRules(Form $form): array
    {
        if (! $blueprint = $this->blueprint($form)) {
            return [];
        }

        $fields = $blueprint->fields();

        return Arr::only($fields->validator()->rules(), $fields->all()->keys()->all());
    }

    public function process(array $config, Form $form): array
    {
        return collect(static::normalizeConnections($config))
            ->map(function (array $connection) use ($form): array {
                $connection = Arr::removeNullValues($connection);

                return Arr::removeNullValues([
                    'id' => Arr::get($connection, 'id') ?? Str::random(8),
                    ...$this->processConnection(Arr::except($connection, ['id', 'enabled', 'conditions']), $form),
                    'enabled' => Arr::get($connection, 'enabled') === false ? false : null,
                    'conditions' => ConnectionLogic::process(Arr::get($connection, 'conditions') ?? []),
                ]);
            })
            ->all();
    }

    protected function processConnection(array $connection, Form $form): array
    {
        if (! $blueprint = $this->blueprint($form)) {
            return $connection;
        }

        return $blueprint->fields()->addValues($connection)->process()->values()->all();
    }

    public function routes(Router $router): void
    {
        //
    }
}
