<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\SampleValue;

class Ranking extends Fieldtype
{
    protected $selectable = false;

    public function previewValue(): mixed
    {
        return SampleValue::optionKeys($this->field());
    }

    public function fakeValue(Generator $faker): mixed
    {
        return $faker->shuffleArray(SampleValue::optionKeys($this->field()));
    }
}
