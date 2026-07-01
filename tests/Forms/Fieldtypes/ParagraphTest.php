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
            'type' => 'paragraph',
            'text' => 'Welcome to our wonderful form!',
            'display' => 'An internal field handle',
        ]));

        $this->assertEquals([
            'type' => 'form_paragraph',
            'text' => 'Welcome to our wonderful form!',
            'hide_display' => true,
            'display' => 'An internal field handle',
            'listable' => false,
        ], $fieldtype->toFieldArray());
    }
}
