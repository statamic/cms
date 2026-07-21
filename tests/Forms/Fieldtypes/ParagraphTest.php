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
            'instructions' => null,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Paragraph)->setField(new FormField('intro_text', [
            'type' => 'paragraph',
            'text' => 'Welcome to our wonderful form!',
            'display' => 'An internal field handle',
            'width' => 50,
            'if' => ['subscribe' => 'is true'],
        ]));

        $this->assertEquals([
            'type' => 'form_paragraph',
            'text' => 'Welcome to our wonderful form!',
            'hide_display' => true,
            'display' => 'An internal field handle',
            'listable' => false,
            'instructions' => null,
            'width' => 50,
            'if' => ['subscribe' => 'is true'],
        ], $fieldtype->toFieldArray());
    }
}
