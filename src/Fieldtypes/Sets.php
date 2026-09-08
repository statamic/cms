<?php

namespace Statamic\Fieldtypes;

use Statamic\Exceptions\ReplicatorIconSetNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Icon;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\FieldTransformer;
use Statamic\Fields\Fieldtype;
use Statamic\Statamic;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Sets extends Fieldtype
{
    protected $selectable = false;

    protected static $setConfigFields = [];

    private const SET_KEYS = ['_id', 'handle', 'display', 'instructions', 'icon', 'image', 'hide', 'fields', 'extraConfig'];

    public static function appendSetConfigFields(array $fields): void
    {
        foreach ($fields as $handle => $config) {
            if (! is_string($handle) || in_array($handle, self::SET_KEYS)) {
                throw new \InvalidArgumentException("Cannot append set config field [{$handle}]. Use a unique field handle.");
            }
        }

        static::$setConfigFields = array_merge(static::$setConfigFields, $fields);
    }

    public static function appendSetConfigField(string $handle, array $config): void
    {
        static::appendSetConfigFields([$handle => $config]);
    }

    private function setConfigFields(): ConfigFields
    {
        return new ConfigFields(collect(static::$setConfigFields)->map(fn ($field, $handle) => compact('handle', 'field')));
    }

    private function preProcessSetConfig(array $set): array
    {
        $values = Arr::except($set, self::SET_KEYS);
        $fields = $this->setConfigFields()->addValues($values)->preProcess();

        if (! $values && $fields->all()->isEmpty()) {
            return [];
        }

        return ['extraConfig' => [
            'values' => array_merge($values, $fields->values()->all()),
            'meta' => $fields->meta()->all(),
        ]];
    }

    private function processSetConfig(array $section): array
    {
        $values = Arr::except($section['extraConfig']['values'] ?? [], self::SET_KEYS);

        return array_merge($values, $this->setConfigFields()->addValues($values)->process()->values()->all());
    }

    public function preload()
    {
        $fields = $this->setConfigFields()->preProcess();

        return ['setConfig' => [
            'fields' => $fields->toPublishArray(),
            'defaults' => [
                'values' => $fields->values()->all(),
                'meta' => $fields->meta()->all(),
            ],
        ]];
    }

    public function extraRules(): array
    {
        return $this->setConfigValidation('rules');
    }

    public function extraValidationAttributes(): array
    {
        return $this->setConfigValidation('attributes');
    }

    private function setConfigValidation(string $method): array
    {
        return collect($this->field->value())->flatMap(function ($tab, $tabIndex) use ($method) {
            return collect($tab['sections'] ?? [])->flatMap(function ($section, $sectionIndex) use ($tabIndex, $method) {
                $prefix = "{$this->field->handle()}.{$tabIndex}.sections.{$sectionIndex}.extraConfig.values.";
                $validator = $this->setConfigFields()
                    ->addValues($section['extraConfig']['values'] ?? [])
                    ->validator()
                    ->withContext(['prefix' => $this->field->validationContext('prefix').$prefix]);

                return collect($validator->{$method}())->mapWithKeys(fn ($value, $handle) => [$prefix.$handle => $value]);
            });
        })->all();
    }

    public function preProcessValidatable($value)
    {
        return collect($value)->map(function ($tab) {
            $tab['sections'] = collect($tab['sections'] ?? [])->map(function ($section) {
                if (static::$setConfigFields) {
                    $values = $section['extraConfig']['values'] ?? [];
                    $section['extraConfig']['values'] = array_merge($values, $this->setConfigFields()
                        ->addValues($values)->preProcessValidatables()->values()->all());
                }

                return $section;
            })->all();

            return $tab;
        })->all();
    }

    /**
     * Converts the "sets" array of a Replicator (or Bard) field into what the
     * <sets-fieldtype> Vue component is expecting, within either the Blueprint
     * or Fieldset builders in the AJAX request performed when opening the field.
     */
    public function preProcess($sets)
    {
        $sets = collect($sets);

        if ($sets->isEmpty()) {
            return [];
        }

        // If the first set doesn't have a "sets" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        if (! Arr::has($sets->first(), 'sets')) {
            $sets = collect([
                'main' => [
                    'display' => __('Main'),
                    'sets' => $sets->all(),
                ],
            ]);
        }

        return collect($sets)->map(function ($group, $groupHandle) {
            return [
                '_id' => $groupId = 'group-'.$groupHandle,
                'handle' => $groupHandle,
                'display' => $group['display'] ?? null,
                'instructions' => $group['instructions'] ?? null,
                'icon' => $group['icon'] ?? null,
                'sections' => collect($group['sets'] ?? [])->map(function ($set, $setHandle) use ($groupId) {
                    return array_merge($this->preProcessSetConfig($set), [
                        '_id' => $setId = $groupId.'-section-'.$setHandle,
                        'handle' => $setHandle,
                        'display' => $set['display'] ?? null,
                        'instructions' => $set['instructions'] ?? null,
                        'icon' => $set['icon'] ?? null,
                        'image' => $this->preProcessPreviewImage($set['image'] ?? null),
                        'hide' => $set['hide'] ?? null,
                        'fields' => collect($set['fields'] ?? [])->map(function ($field, $i) use ($setId) {
                            return array_merge(FieldTransformer::toVue($field), ['_id' => $setId.'-'.$i]);
                        })->all(),
                    ]);
                })->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Converts the "sets" array of a Replicator (or Bard) field into what
     * the <replicator-fieldtype> is expecting in its config.sets array.
     */
    public function preProcessConfig($sets)
    {
        $sets = collect($sets);

        if ($sets->isEmpty()) {
            return [];
        }

        // If the first set doesn't have a "sets" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        if (! Arr::has($sets->first(), 'sets')) {
            $sets = collect([
                'main' => [
                    'sets' => $sets->all(),
                ],
            ]);
        }

        return collect($sets)->map(function ($group, $groupHandle) {
            return array_merge($group, [
                'handle' => $groupHandle,
                'sets' => collect($group['sets'])
                    ->map(function ($config, $name) {
                        return array_merge($config, [
                            'handle' => $name,
                            'id' => $name,
                            'image' => $this->previewImageUrl($config['image'] ?? null),
                            'fields' => (new NestedFields)->preProcessConfig(Arr::get($config, 'fields', [])),
                        ]);
                    })
                    ->values()
                    ->all(),
            ]);
        })->values()->all();
    }

    /**
     * Converts the Blueprint/Fieldset builder Settings Vue component's representation of the
     * Replicator's "sets" array into what should be saved to the Blueprint/Fieldset's YAML.
     * Triggered in the AJAX request when you click "finish" when editing a Replicator field.
     */
    public function process($tabs)
    {
        return collect($tabs)->mapWithKeys(function ($tab) {
            return [
                $tab['handle'] => [
                    'display' => $tab['display'],
                    'instructions' => $tab['instructions'] ?? null,
                    'icon' => $tab['icon'] ?? null,
                    'sets' => collect($tab['sections'])->mapWithKeys(function ($section) {
                        return [
                            $section['handle'] => array_merge($this->processSetConfig($section), [
                                'display' => $section['display'],
                                'instructions' => $section['instructions'] ?? null,
                                'icon' => $section['icon'] ?? null,
                                'image' => $this->processPreviewImage($section['image'] ?? null),
                                'hide' => $section['hide'] ?? null,
                                'fields' => collect($section['fields'])->map(function ($field) {
                                    return FieldTransformer::fromVue($field);
                                })->all(),
                            ]),
                        ];
                    })->all(),
                ],
            ];
        })
            ->all();
    }

    /**
     * Allow the user to define a custom icon set.
     */
    public static function useIcons(string $name, ?string $directory = null): void
    {
        if ($directory) {
            Icon::register($name, $directory);
        } elseif (! Icon::sets()->has($name)) {
            throw new ReplicatorIconSetNotFoundException($name);
        }

        // Provide to script for <icon-fieldtype> selector components in blueprint config.
        // If a directory hasn't been provided, it will assume they've manually registered the set.
        Statamic::provideToScript(['replicatorSetIcons' => $name]);
    }

    private function preProcessPreviewImage($image)
    {
        if (! $image) {
            return null;
        }

        ['container' => $container, 'folder' => $folder] = static::previewImageConfig();

        $prefix = sprintf('%s::%s', $container, $folder ? $folder.'/' : '');

        return $prefix.$image;
    }

    private function processPreviewImage($image)
    {
        if (! $image) {
            return null;
        }

        ['container' => $container, 'folder' => $folder] = static::previewImageConfig();

        $prefix = sprintf('%s::%s', $container, $folder ? $folder.'/' : '');

        return (string) str($image)->after($prefix);
    }

    private function previewImageUrl($image)
    {
        if (! $path = $this->preProcessPreviewImage($image)) {
            return null;
        }

        return Asset::find($path)?->thumbnailUrl();
    }

    public static function previewImageConfig(): ?array
    {
        if (! $config = config('statamic.assets.set_preview_images')) {
            return null;
        }

        if (is_string($config)) {
            $config = ['container' => $config];
        }

        $container = $config['container'] ?? null;
        $folder = $config['folder'] ?? null;

        if (! AssetContainer::find($container)) {
            return null;
        }

        return compact('container', 'folder');
    }
}
