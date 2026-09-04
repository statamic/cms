<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\MultiChoice;
use Tests\TestCase;

class MultiChoiceTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ]));

        $this->assertEquals([
            'type' => 'radio',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'default' => 'red',
        ]));

        $this->assertEquals([
            'type' => 'radio',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'default' => 'red',
        ], $fieldtype->toFieldArray());
    }
}
