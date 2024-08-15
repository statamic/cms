<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Site;
use Statamic\Support\Arr;

class FieldTransformer
{
    public static function fromVue(array $submitted)
    {
        $method = $submitted['type'].'TabField';

        return static::$method($submitted);
    }

    private static function importTabField(array $submitted)
    {
        return array_filter([
            'import' => $submitted['fieldset'],
            'prefix' => $submitted['prefix'] ?? null,
        ]);
    }

    private static function inlineTabField(array $submitted)
    {
        $fieldtype = FieldtypeRepository::find($submitted['fieldtype'] ?? $submitted['config']['type']);

        $fields = Field::commonFieldOptions()->all()
            ->merge($fieldtype->configFields()->all());

        $field = collect($submitted['config'])
            ->reject(function ($value, $key) use ($fields) {
                if (in_array($key, ['isNew', 'icon'])) {
                    return true;
                }

                if ($key === 'width' && $value === 100) {
                    return true;
                }

                if ($key === 'localizable' && $value === false && ! Site::multiEnabled()) {
                    return true;
                }

                if (! $field = $fields->get($key)) {
                    return false;
                }

                if ($field->mustRemainInConfig()) {
                    return false;
                }

                return $field->defaultValue() === $value;
            })
            ->map(function ($value, $key) {
                if ($key === 'sets') {
                    return collect($value)
                        ->map(function ($setGroup) {
                            if (! Arr::get($setGroup, 'instructions')) {
                                unset($setGroup['instructions']);
                            }

                            if (! Arr::get($setGroup, 'icon')) {
                                unset($setGroup['icon']);
                            }

                            if (isset($setGroup['sets'])) {
                                $setGroup['sets'] = collect($setGroup['sets'])
                                    ->map(function ($set) {
                                        if (! Arr::get($set, 'instructions')) {
                                            unset($set['instructions']);
                                        }

                                        if (! Arr::get($set, 'icon')) {
                                            unset($set['icon']);
                                        }

                                        if (! Arr::get($set, 'hide')) {
                                            unset($set['hide']);
                                        }

                                        return $set;
                                    })
                                    ->filter()
                                    ->all();
                            }

                            return $setGroup;
                        })
                        ->all();
                }

                return $value;
            })
            ->sortBy(function ($value, $key) {
                // Push sets & fields to the end of the config.
                if ($key === 'sets' || $key === 'fields') {
                    return 2;
                }

                return 1;
            })
            ->all();

        return array_filter([
            'handle' => $submitted['handle'],
            'field' => Arr::removeNullValues($field),
        ]);
    }

    private static function referenceTabField(array $submitted)
    {
        $config = Arr::removeNullValues(Arr::only($submitted['config'], $submitted['config_overrides']));

        return array_filter([
            'handle' => $submitted['handle'],
            'field' => $submitted['field_reference'],
            'config' => $config,
        ]);
    }

    public static function toVue($field): array
    {
        if (isset($field['import'])) {
            return static::importFieldToVue($field);
        }

        return is_string($field['field'])
            ? static::referenceFieldToVue($field)
            : static::inlineFieldToVue($field);
    }

    private static function referenceFieldToVue($field): array
    {
        $fieldsetField = static::fieldsetFields()[$field['field']] ?? [];

        $mergedConfig = array_merge(
            $fieldsetFieldConfig = Arr::get($fieldsetField, 'config', []),
            $config = Arr::get($field, 'config', [])
        );

        $mergedConfig['width'] = $mergedConfig['width'] ?? 100;
        $mergedConfig['localizable'] = $mergedConfig['localizable'] ?? false;

        return [
            'handle' => $field['handle'],
            'type' => 'reference',
            'field_reference' => $field['field'],
            'config' => $mergedConfig,
            'config_overrides' => array_keys($config),
            'fieldtype' => $type = $mergedConfig['type'],
            'icon' => FieldtypeRepository::find($type)->icon(),
        ];
    }

    private static function inlineFieldToVue($field): array
    {
        $config = $field['field'];
        $config['width'] = $config['width'] ?? 100;
        $config['localizable'] = $config['localizable'] ?? false;
        $config = static::normalizeRequiredValidation($config);
        $config = static::normalizeVisibility($config);

        return [
            'handle' => $field['handle'],
            'type' => 'inline',
            'config' => $config,
            'fieldtype' => $type = $config['type'] ?? 'text',
            'icon' => FieldtypeRepository::find($type)->icon(),
        ];
    }

    private static function importFieldToVue($field): array
    {
        return [
            'type' => 'import',
            'fieldset' => $field['import'],
            'prefix' => $field['prefix'] ?? null,
        ];
    }

    public static function fieldsetFields()
    {
        if (app()->has($binding = 'statamic.fieldset.fields')) {
            return app($binding);
        }

        $fields = Fieldset::all()->flatMap(function ($fieldset) {
            return collect($fieldset->fields()->all())->mapWithKeys(function ($field, $handle) use ($fieldset) {
                return [$fieldset->handle().'.'.$field->handle() => [
                    'display' => $field->display(),
                    'config' => $field->config(),
                    'fieldset' => [
                        'handle' => $fieldset->handle(),
                        'title' => $fieldset->title(),
                    ],
                ]];
            });
        })->sortBy('display')->all();

        app()->instance($binding, $fields);

        return $fields;
    }

    protected static function normalizeRequiredValidation($config)
    {
        if (Arr::get($config, 'required') !== true) {
            return $config;
        }

        $validate = Arr::get($config, 'validate', []);

        if (is_string($validate)) {
            $validate = explode('|', $validate);
        }

        $validate = collect($validate);

        if (! $validate->contains('required')) {
            $validate->prepend('required');
        }

        Arr::forget($config, 'required');
        Arr::set($config, 'validate', $validate->all());

        return $config;
    }

    protected static function normalizeVisibility($config)
    {
        $visibility = Arr::get($config, 'visibility');

        $legacyReadOnly = Arr::pull($config, 'read_only');

        if ($legacyReadOnly && ! $visibility) {
            $config['visibility'] = 'read_only';
        }

        return $config;
    }
}
