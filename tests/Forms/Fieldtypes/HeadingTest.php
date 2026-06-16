<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Heading;
use Tests\TestCase;

class HeadingTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Heading)->setField(new FormField('section_heading', [
            'type' => 'form_heading',
            'display' => 'This is a heading',
            'subheading' => 'This is a subheading',
        ]));

        $this->assertEquals([
            'type' => 'form_heading',
            'display' => 'This is a heading',
            'subheading' => 'This is a subheading',
            'hide_display' => true,
            'listable' => false,
        ], $fieldtype->toFieldArray());
    }
}
