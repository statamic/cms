<?php

namespace Statamic\Forms;

use Illuminate\Support\Arr;
use Statamic\Fields\Field;

class FieldOptions
{
    public static function keys(Field $field): array
    {
        $options = Arr::wrap($field->get('options', []));

        if ($options === []) {
            return [];
        }

        if (Arr::isAssoc($options)) {
            return array_keys($options);
        }

        return array_values(array_filter(array_map(function ($option) {
            if (! is_array($option)) {
                return $option;
            }

            return Arr::get($option, 'key') ?? Arr::get($option, 'value');
        }, $options), fn ($value) => $value !== null && $value !== ''));
    }
}
