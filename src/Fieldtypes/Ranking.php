<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Illuminate\Validation\Rule;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\SampleValue;

class Ranking extends Fieldtype
{
    protected $selectable = false;

    public function rules(): array
    {
        return ['array', Rule::in(SampleValue::optionKeys($this->field()))];
    }

    public function previewValue(): mixed
    {
        return SampleValue::optionKeys($this->field());
    }

    public function fakeValue(Generator $faker): mixed
    {
        return $faker->shuffleArray(SampleValue::optionKeys($this->field()));
    }
}
