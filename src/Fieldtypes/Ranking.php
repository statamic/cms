<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\FieldOptions;

class Ranking extends Fieldtype
{
    protected $selectable = false;

    public function previewValue(): mixed
    {
        return FieldOptions::keys($this->field());
    }

    public function fakeValue(Generator $faker): mixed
    {
        return $faker->shuffleArray(FieldOptions::keys($this->field()));
    }
}
