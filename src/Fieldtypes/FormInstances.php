<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Forms\ConfigFields;
use Statamic\Forms\Fieldtype as FormFieldtype;
use Statamic\GraphQL\Types\FormType;
use Statamic\Statamic;

use function Statamic\trans as __;

class FormInstances extends FormFieldtype
{
    protected static $handle = 'form_instances';
    protected $component = 'form_instances';
    protected $icon = 'fieldtype-form';
    protected $selectable = false;

    public static function forEntry(Entry $entry): array
    {
        $field = $entry->blueprint()->fields()->get(static::handle());

        if (! $field || $field->type() !== static::handle()) {
            return [];
        }

        $forms = static::formsForCollection($entry->collectionHandle());

        return collect(static::toMap($entry->value(static::handle())))
            ->filter(fn ($config, $handle) => $forms->has($handle))
            ->all();
    }

    public static function formsForCollection(?string $collection): Collection
    {
        if (! $collection || ! Statamic::formsProInstalled()) {
            return collect();
        }

        return Facades\Form::all()
            ->filter(fn (FormContract $form) => in_array($collection, $form->uniqueInstancesCollections()))
            ->keyBy->handle();
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                        'type' => 'radio',
                        'default' => 'default',
                        'options' => [
                            'default' => __('Stack Selector'),
                            'select' => __('Select Dropdown'),
                            'typeahead' => __('Typeahead Field'),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function preProcess($data)
    {
        $map = static::toMap($data);

        return [
            'forms' => array_keys($map),
            'config' => collect($map)->map(fn ($config, $handle) => $this->preProcessConfigFor($handle, $config))->all(),
        ];
    }

    private function preProcessConfigFor(string $handle, array $config): array
    {
        if (! $form = $this->configurableForm($handle)) {
            return [];
        }

        return $this->overrideFields($form)->addValues($config)->preProcess()->values()->only(array_keys($config))->all();
    }

    public function preProcessConfig($data)
    {
        return array_keys(static::toMap($data));
    }

    public function process($data)
    {
        $stored = $this->storedValue();

        $map = collect($data['forms'] ?? [])
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($handle) => [$handle => $this->processConfigFor($handle, $data['config'][$handle] ?? [], $stored[$handle] ?? [])])
            ->all();

        return $map ?: null;
    }

    private function processConfigFor(string $handle, $config, array $stored): array
    {
        if (! $form = $this->configurableForm($handle)) {
            return $stored;
        }

        $config = is_array($config) ? $config : [];

        // Only desynced fields are submitted, so a key's presence means it's an
        // override. Falsy values count; an override is only "empty" when blank.
        return $this->overrideFields($form)
            ->addValues($config)
            ->process()
            ->values()
            ->only(array_keys($config))
            ->reject(fn ($value) => $value === null || $value === '')
            ->all();
    }

    private function storedValue(): array
    {
        if ($this->field->parentField() || ! $entry = $this->parentEntry()) {
            return [];
        }

        return static::toMap($entry->value($this->field->handle()));
    }

    public function preProcessValidatable($value)
    {
        return $value['forms'] ?? [];
    }

    private function configurableForm(string $handle): ?FormContract
    {
        if (! Statamic::formsProInstalled() || ! $form = Facades\Form::find($handle)) {
            return null;
        }

        if (($user = User::current()) && ! $user->can('edit', $form)) {
            return null;
        }

        return $form;
    }

    private static function toMap($value): array
    {
        return collect(is_array($value) ? $value : [])
            ->map(fn ($config) => is_array($config) ? $config : [])
            ->all();
    }

    protected function selectableForms(): Collection
    {
        $forms = static::formsForCollection($this->collectionHandle());

        return parent::selectableForms()->filter(fn (FormContract $form) => $forms->has($form->handle()));
    }

    protected function getBaseSelectionsUrlParameters()
    {
        return ['collection' => $this->collectionHandle()];
    }

    private function collectionHandle(): ?string
    {
        return $this->parentEntry()?->collectionHandle()
            ?? $this->config('collection')
            ?? request()->input('collection');
    }

    public function preload()
    {
        $data = parent::preload();

        $forms = collect($this->field->value()['forms'] ?? [])
            ->merge(static::formsForCollection($this->parentEntry()?->collectionHandle())->keys())
            ->unique();

        $data['submissions'] = $forms
            ->mapWithKeys(fn ($handle) => [$handle => $this->submissionsMeta($handle)])
            ->filter()
            ->all();

        $data['configureMeta'] = $forms
            ->mapWithKeys(fn ($handle) => [$handle => $this->configureMeta($handle)])
            ->filter()
            ->all();

        return $data;
    }

    private function configureMeta(string $handle): ?array
    {
        if (! $this->parentEntry() || ! $form = $this->configurableForm($handle)) {
            return null;
        }

        if (! $form->hasUniqueInstances()) {
            return null;
        }

        $config = $this->field->value()['config'][$handle] ?? [];

        $formData = [...$form->data()->all(), 'connections' => $form->connections()->all()];

        $origin = $this->overrideFields($form)->addValues($formData)->preProcess();
        $fields = $this->overrideFields($form)->addValues(array_merge($formData, $config))->preProcess();

        return [
            'blueprint' => $this->overrideBlueprint($form)->toPublishArray(),
            'meta' => $fields->meta(),
            'originValues' => $origin->values()->all(),
            'originMeta' => $origin->meta(),
        ];
    }

    private function overrideFields(FormContract $form): Fields
    {
        return $this->overrideBlueprint($form)->fields();
    }

    private function overrideBlueprint(FormContract $form): Blueprint
    {
        $access = ConfigFields::fields()['access'];
        $access['fields'] = collect($access['fields'])->map(fn (array $field): array => [
            ...$field,
            'localizable' => true,
        ]);

        $connections = [
            'display' => __('Connections'),
            'fields' => [
                'connections' => [
                    'type' => 'form_connections',
                    'display' => __('Connections'),
                    'instructions' => __('statamic::messages.form_fieldtype_connections_instructions'),
                    'localizable' => true,
                    'form' => $form->handle(),
                ],
            ],
        ];

        return Facades\Blueprint::make()->setContents([
            'tabs' => ['main' => ['sections' => [
                $this->toBlueprintSection($access),
                $this->toBlueprintSection($connections),
            ]]],
        ]);
    }

    private function toBlueprintSection(array $section): array
    {
        return [
            'display' => $section['display'],
            'fields' => collect($section['fields'])
                ->map(fn (array $field, string $handle): array => ['handle' => $handle, 'field' => $field])
                ->values()
                ->all(),
        ];
    }

    public function getItemData($values)
    {
        return parent::getItemData($values['forms'] ?? $values);
    }

    protected function collect($value)
    {
        return collect(array_keys(static::toMap($value)));
    }

    public function shallowAugment($values)
    {
        return parent::shallowAugment(array_keys(static::toMap($values)));
    }

    public function toQueryableValue($value)
    {
        return array_keys(static::toMap($value)) ?: null;
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type(FormType::NAME));
    }
}
