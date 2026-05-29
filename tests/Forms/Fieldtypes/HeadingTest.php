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
        ]));

        $this->assertEquals([
            'type' => 'form_heading',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Heading)->setField(new FormField('section_heading', [
            'type' => 'form_heading',
            'display' => 'Contact Information',
        ]));

        $this->assertEquals([
            'type' => 'form_heading',
            'display' => 'Contact Information',
        ], $fieldtype->toFieldArray());
    }
}
