<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;

class StarRating extends Fieldtype
{
    protected $selectable = false;

    public function previewValue(): mixed
    {
        $min = $this->field()->get('min');

        return is_numeric($min) ? $min + 0 : 1;
    }

    public function fakeValue(Generator $faker): mixed
    {
        $max = max(1, (int) ($this->field()->get('max_stars') ?? 5));

        if (! $this->field()->get('allow_half_stars')) {
            return $faker->numberBetween(1, $max);
        }

        $value = $faker->numberBetween(1, $max * 2) * 0.5;

        return fmod($value, 1.0) === 0.0 ? (int) $value : $value;
    }
}
