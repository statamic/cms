<?php

namespace Statamic\Fields;

use Faker\Generator;
use Illuminate\Support\Arr;

use function Statamic\trans as __;

class SampleValue
{
    private const RANDOMIZED_DEFAULTS = [
        'toggle', 'checkboxes', 'select', 'radio', 'button_group', 'image_choice', 'yes_no', 'ranking', 'dictionary',
    ];

    public static function fake(Field $field, Generator $faker): mixed
    {
        $default = $field->defaultValue();

        if ($default !== null && ! in_array($field->type(), self::RANDOMIZED_DEFAULTS)) {
            return $default;
        }

        return $field->fieldtype()->fakeValue($faker) ?? $default;
    }

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
