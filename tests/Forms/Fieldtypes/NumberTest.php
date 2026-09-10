<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Number;
use Tests\TestCase;

class NumberTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Number)->setField(new FormField('age', [
            'type' => 'number',
            'min' => 0,
            'max' => 120,
        ]));

        $this->assertEquals([
            'type' => 'integer',
            'min' => 0,
            'max' => 120,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Number)->setField(new FormField('quantity', [
            'type' => 'number',
            'min' => 1,
            'max' => 10,
            'default' => 1,
        ]));

        $this->assertEquals([
            'type' => 'integer',
            'min' => 1,
            'max' => 10,
            'default' => 1,
        ], $fieldtype->toFieldArray());
    }
}
