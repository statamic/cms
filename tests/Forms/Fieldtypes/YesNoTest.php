<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\YesNo;
use Tests\TestCase;

class YesNoTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_yes_no_options()
    {
        $fieldtype = (new YesNo)->setField(new FormField('recommend', [
            'type' => 'yes_no',
        ]));

        $this->assertEquals([
            'type' => 'radio',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new YesNo)->setField(new FormField('recommend', [
            'type' => 'yes_no',
            'default' => 'yes',
            'inline' => true,
        ]));

        $this->assertEquals([
            'type' => 'radio',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
            'default' => 'yes',
            'inline' => true,
        ], $fieldtype->toFieldArray());
    }
}
