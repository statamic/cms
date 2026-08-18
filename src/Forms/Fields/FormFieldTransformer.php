<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Facades\Fieldset;
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
                if ($key === 'isNew') {
                    return true;
                }

                if ($key === 'icon' && ! $fields->has('icon')) {
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
            Arr::get($fieldsetField, 'config', []),
            $config = Arr::get($field, 'config', [])
        );

        $mergedConfig['width'] = $mergedConfig['width'] ?? 100;
        $mergedConfig['hidden'] = $mergedConfig['hidden'] ?? false;

        $formField = new FormField($field['handle'], $mergedConfig);
        $formFieldtype = FormFieldtypeRepository::find($mergedConfig['type'])->setField($formField);

        return [
            'handle' => $field['handle'],
            'type' => 'reference',
            'field_reference' => $field['field'],
            'config' => $mergedConfig,
            'config_overrides' => array_keys($config),
            'fieldtype' => $mergedConfig['type'],
            'icon' => $formFieldtype->icon(),
            'preview' => static::fieldtypePreview($formFieldtype),
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
        $import = [
            '_id' => uniqid(),
            'type' => 'import',
            'fieldset' => $field['import'],
            'prefix' => $field['prefix'] ?? null,
            'previews' => static::fieldsetPreviews($field['import']),
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
                ? new Field($fieldtype->toArray()['handle'], $fieldtype->field()->config())
                : $fieldtype->toField();

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

    private static function fieldsetPreviews(string $handle): array
    {
        $fieldset = Fieldset::find($handle);

        if (! $fieldset) {
            return [];
        }

        return collect($fieldset->fields()->all())
            ->mapWithKeys(fn (Field $field) => [$field->handle() => [
                ...static::fieldPreview($field),
                'icon' => $field->fieldtype()->icon(),
            ]])
            ->all();
    }

    private static function fieldPreview(Field $field): ?array
    {
        try {
            $field = new Field('preview', $field->config());
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
