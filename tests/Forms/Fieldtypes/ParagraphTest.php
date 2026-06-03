<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Paragraph;
use Tests\TestCase;

class ParagraphTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Paragraph)->setField(new FormField('intro_text', [
            'type' => 'form_paragraph',
        ]));

        $this->assertEquals([
            'type' => 'form_paragraph',
            'hide_display' => true,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Paragraph)->setField(new FormField('intro_text', [
            'type' => 'form_paragraph',
            'display' => 'Introduction',
        ]));

        $this->assertEquals([
            'type' => 'form_paragraph',
            'hide_display' => true,
            'display' => 'Introduction',
        ], $fieldtype->toFieldArray());
    }
}
