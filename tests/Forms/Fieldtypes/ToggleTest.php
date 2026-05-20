<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Toggle;
use Tests\TestCase;

class ToggleTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Toggle)->setField(new FormField('agree', [
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
        ]));

        $this->assertEquals([
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Toggle)->setField(new FormField('agree', [
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
            'default' => true,
        ]));

        $this->assertEquals([
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
            'default' => true,
        ], $fieldtype->toFieldArray());
    }
}
