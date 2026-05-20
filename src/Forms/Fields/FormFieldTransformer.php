<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Support\Arr;

class FormFieldTransformer
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
}
