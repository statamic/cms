<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Spacer;
use Tests\TestCase;

class SpacerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Spacer)->setField(new FormField('my_spacer', [
            'type' => 'spacer',
        ]));

        $this->assertEquals([
            'type' => 'spacer',
            'hide_display' => true,
        ], $fieldtype->toFieldArray());
    }
}
