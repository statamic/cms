<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;

class OpinionScale extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'scaleValues' => $this->scaleValues(),
        ];
    }

    public function extraRenderableFieldData(): array
    {
        return [
            'scale_values' => $this->scaleValues(),
        ];
    }

    private function scaleValues(): array
    {
        return range($this->config('min'), $this->config('max'));
    }

    public function previewValue(): mixed
    {
        $min = $this->field()->get('min');

        return is_numeric($min) ? $min + 0 : 1;
    }

    public function fakeValue(Generator $faker): mixed
    {
        $min = (int) ($this->field()->get('min') ?? 0);
        $max = (int) ($this->field()->get('max') ?? 10);

        return $faker->numberBetween(min($min, $max), max($min, $max));
    }
}
