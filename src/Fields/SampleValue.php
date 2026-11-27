<?php

namespace Statamic\Fields;

use Faker\Generator;
use Illuminate\Support\Arr;

use function Statamic\trans as __;

class SampleValue
{
    public static function sentence(): string
    {
        return __('Lorem ipsum dolor sit amet');
    }

    public static function paragraph(): string
    {
        return __('statamic::messages.sample_value_paragraph');
    }

    public static function randomKeys(array $keys, Generator $faker, mixed $max = null): array
    {
        if ($keys === []) {
            return [];
        }

        $limit = min((int) $max > 0 ? (int) $max : 3, count($keys));

        return $faker->randomElements($keys, $faker->numberBetween(1, $limit));
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
