<?php

namespace Statamic\Fields;

use Statamic\Support\Arr;

class FieldTransformer
{
    public static function fromVue(array $submitted)
    {
        $method = $submitted['type'] . 'SectionField';

        return static::$method($submitted);
    }

    private static function importSectionField(array $submitted)
    {
        return array_filter([
            'import' => $submitted['fieldset'],
            'prefix' => $submitted['prefix'] ?? null
        ]);
    }

    private static function inlineSectionField(array $submitted)
    {
        return array_filter([
            'handle' => $submitted['handle'],
            'field' => Arr::removeNullValues(array_except($submitted['config'], ['isNew']))
        ]);
    }

    private static function referenceSectionField(array $submitted)
    {
        return array_filter([
            'handle' => $submitted['handle'],
            'field' => $submitted['field_reference'],
            'config' => Arr::removeNullValues(array_only($submitted['config'], $submitted['config_overrides']))
        ]);
    }

    public static function toVue($field): array
    {
        if (isset($field['import'])) {
            return static::importFieldToVue($field);
        }

        return (is_string($field['field']))
            ? static::referenceFieldToVue($field)
            : static::inlineFieldToVue($field);
    }

    private static function referenceFieldToVue($field): array
    {
        $fieldsetField = array_get(static::fieldsetFields(), $field['field'], []);

        $mergedConfig = array_merge(
            $fieldsetFieldConfig = array_get($fieldsetField, 'config', []),
            $config = array_get($field, 'config', [])
        );

        $mergedConfig['width'] = $mergedConfig['width'] ?? 100;

        return [
            'handle' => $field['handle'],
            'type' => 'reference',
            'field_reference' => $field['field'],
            'config' => $mergedConfig,
            'config_overrides' => array_keys($config),
            'fieldtype' => $fieldsetField['type'],
        ];
    }

    private static function inlineFieldToVue($field): array
    {
        $config = $field['field'];
        $config['width'] = $config['width'] ?? 100;

        return [
            'handle' => $field['handle'],
            'type' => 'inline',
            'config' => $config,
            'fieldtype' => $config['type'] ?? 'text',
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
}
