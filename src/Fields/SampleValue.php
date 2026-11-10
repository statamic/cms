<?php

namespace Statamic\Fields;

use Illuminate\Support\Arr;

use function Statamic\trans as __;

class SampleValue
{
    public static function placeholder(Field $field): string
    {
        return '['.__($field->display()).']';
    }

    public static function optionKeys(Field $field): array
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
