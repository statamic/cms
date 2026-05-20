<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Fields\Field;
use Statamic\Fields\FieldTransformer;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Support\Arr;

class FormFieldTransformer extends FieldTransformer
{
    public static function fromVue(array $submitted)
    {
        $method = $submitted['type'].'TabField';

        return static::$method($submitted);
    }

    private static function importTabField(array $submitted)
    {
        dd('TODO');

        return array_filter([
            'import' => $submitted['fieldset'],
            'prefix' => $submitted['prefix'] ?? null,
            'section_behavior' => ($submitted['section_behavior'] ?? 'preserve') === 'flatten'
                ? 'flatten'
                : null,
        ]);
    }

    private static function inlineTabField(array $submitted)
    {
        $fieldtype = FormFieldtypeRepository::find($submitted['fieldtype']);

        $fields = FormField::commonFieldOptions()->all()
            ->merge($fieldtype->configFields()->all());

        $field = collect($submitted['config'])
            ->reject(function ($value, $key) use ($fields) {
                if ($key === 'icon') {
                    return true;
                }

                if ($key === 'hidden' && $value === false) {
                    return true;
                }

                if ($key === 'width' && $value === 100) {
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
            ->all();

        return array_filter([
            'handle' => $submitted['handle'],
            'field' => Arr::removeNullValues($field),
        ]);
    }

    private static function referenceTabField(array $submitted)
    {
        dd('TODO');

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
        dd('TODO');

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
        $config['hidden'] = $config['hidden'] ?? false;
        $config = static::normalizeRequiredValidation($config);
        $config = static::normalizeVisibility($config);

        $formField = new FormField($field['handle'], $config);
        $formFieldtype = FormFieldtypeRepository::find($config['type'])->setField($formField);

        return [
            'handle' => $field['handle'],
            'type' => 'inline',
            'config' => $config,
            'fieldtype' => $config['type'] ?? 'short_answer',
            'icon' => $formFieldtype->icon(),
            'preview' => static::fieldtypePreview($formFieldtype),
        ];
    }

    private static function importFieldToVue($field): array
    {
        dd('TODO');

        $import = [
            'type' => 'import',
            'fieldset' => $field['import'],
            'prefix' => $field['prefix'] ?? null,
        ];

        if (isset($field['section_behavior'])) {
            $import['section_behavior'] = $field['section_behavior'];
        }

        return $import;
    }

    private static function fieldtypePreview(FormFieldtype $fieldtype): ?array
    {
        try {
            $field = $fieldtype instanceof Fallback
                ? new Field($fieldtype->toArray()['handle'], ['type' => $fieldtype->toArray()['handle']])
                : (clone $fieldtype)->setField(new FormField($fieldtype->handle(), ['type' => $fieldtype->handle()]))->toField();

            $field->setValue($field->defaultValue());

            return [
                'config' => $field->toPublishArray(),
                'value' => $field->fieldtype()->preProcess($field->value()),
                'meta' => $field->fieldtype()->preload(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
