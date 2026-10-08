<?php

namespace Statamic\Forms\Connectors;

use Illuminate\Contracts\Validation\CompilableRules;
use Illuminate\Routing\Router;
use LogicException;
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
    protected ?Form $form = null;
    protected array $connections = [];

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_connector');
    }

    public function setForm(Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function form(): Form
    {
        if (! $this->form) {
            throw new LogicException('No form has been set on the ['.static::handle().'] connector.');
        }

        return $this->form;
    }

    public function setConnections(array $connections): static
    {
        $this->connections = static::normalizeConnections($connections);

        return $this;
    }

    public function connections(): array
    {
        return $this->connections;
    }

    public function forForm(Form $form): static
    {
        return $this->setForm($form)->setConnections($form->connections()->get(static::handle(), []));
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

    public function count(): ?int
    {
        return count($this->connections());
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function finalized(Submission $submission): object|array
    {
        return collect($this->connections())
            ->filter(fn (array $connection) => ConnectionLogic::passes($connection, $submission))
            ->map(fn (array $connection) => $this->job($submission, $connection))
            ->filter()
            ->values()
            ->all();
    }

    abstract protected function job(Submission $submission, array $connection): ?object;

    abstract public function render(): VueComponent;

    abstract public function blueprint(): Blueprint;

    protected function connectionFields(array $connection): Fields
    {
        return $this->blueprint()->fields()->addValues($connection)->preProcess();
    }

    protected function connectionMeta(array $connection): array
    {
        return $this->connectionFields($connection)->meta()->all();
    }

    protected function blueprintProps(): array
    {
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->preProcess();

        return [
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => collect($this->connections())
                ->filter(fn (array $connection): bool => isset($connection['id']))
                ->mapWithKeys(fn (array $connection): array => [
                    $connection['id'] => $this->connectionMeta($connection),
                ])
                ->all(),
            'defaults' => [
                'values' => $fields->values()->all(),
                'meta' => $fields->meta()->all(),
            ],
        ];
    }

    public function preProcess(array $connections): array
    {
        return collect(static::normalizeConnections($connections))
            ->map(fn (array $connection): array => [
                ...$this->preProcessConnection($connection),
                'id' => Arr::get($connection, 'id') ?? Str::random(8),
                'enabled' => Arr::get($connection, 'enabled') !== false,
                'conditions' => ConnectionLogic::preProcess(Arr::get($connection, 'conditions') ?? []),
            ])
            ->all();
    }

    protected function preProcessConnection(array $connection): array
    {
        return $this->connectionFields($connection)->values()->all();
    }

    public function rules(): array
    {
        $connectionRules = collect($this->connectionRules());

        // Laravel only compiles rules like Rule::forEach() when they're the key's entire value, so they can't be merged.
        [$compilable, $connectionRules] = $connectionRules->partition(fn ($rules) => $rules instanceof CompilableRules);

        return [
            '*' => ['array'],
            ...collect($this->blueprintRules())
                ->mergeRecursive($connectionRules->map(fn ($rules) => is_object($rules) ? [$rules] : Validator::explodeRules($rules)))
                ->merge($compilable)
                ->mapWithKeys(fn ($rules, $key) => ['*.'.$key => $rules])
                ->all(),
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ];
    }

    protected function connectionRules(): array
    {
        return [];
    }

    // Only top-level field rules can be derived without values. Grid sub-field rules belong in connectionRules().
    private function blueprintRules(): array
    {
        $fields = $this->blueprint()->fields();

        return Arr::only($fields->validator()->rules(), $fields->all()->keys()->all());
    }

    public function process(array $connections): array
    {
        return collect(static::normalizeConnections($connections))
            ->map(function (array $connection): array {
                $connection = Arr::removeNullValues($connection);

                return Arr::removeNullValues([
                    'id' => Arr::get($connection, 'id') ?? Str::random(8),
                    ...$this->processConnection(Arr::except($connection, ['id', 'enabled', 'conditions'])),
                    'enabled' => Arr::get($connection, 'enabled') === false ? false : null,
                    'conditions' => ConnectionLogic::process(Arr::get($connection, 'conditions') ?? []),
                ]);
            })
            ->all();
    }

    protected function processConnection(array $connection): array
    {
        return $this->blueprint()->fields()->addValues($connection)->process()->values()->all();
    }

    public function routes(Router $router): void
    {
        //
    }
}
