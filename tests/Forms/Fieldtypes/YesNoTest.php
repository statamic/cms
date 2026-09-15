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
            'type' => 'yes_no',
            'appearance' => 'chips',
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
            'display' => 'Would you recommend this product?',
        ]));

        $this->assertEquals([
            'type' => 'yes_no',
            'appearance' => 'chips',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
            'display' => 'Would you recommend this product?',
        ], $fieldtype->toFieldArray());
    }
}
