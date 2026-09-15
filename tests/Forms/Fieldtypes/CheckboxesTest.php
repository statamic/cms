<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Checkboxes;
use Tests\TestCase;

class CheckboxesTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Checkboxes)->setField(new FormField('interests', [
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
                'reading' => 'Reading',
            ],
        ]));

        $this->assertEquals([
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
                'reading' => 'Reading',
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Checkboxes)->setField(new FormField('interests', [
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
            ],
            'default' => ['music'],
        ]));

        $this->assertEquals([
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
            ],
            'default' => ['music'],
        ], $fieldtype->toFieldArray());
    }
}
