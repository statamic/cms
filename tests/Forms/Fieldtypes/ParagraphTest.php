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
        ]));

        $this->assertEquals([
            'type' => 'html',
            'html' => null,
            'hide_display' => true,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_parses_markdown_content_to_html()
    {
        $fieldtype = (new Paragraph)->setField(new FormField('intro_text', [
            'type' => 'paragraph',
            'content' => 'Welcome to our **form**!',
        ]));

        $this->assertEquals([
            'type' => 'html',
            'html' => "<p>Welcome to our <strong>form</strong>!</p>\n",
            'hide_display' => true,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Paragraph)->setField(new FormField('intro_text', [
            'type' => 'paragraph',
            'content' => 'Hello',
            'display' => 'Introduction',
        ]));

        $this->assertEquals([
            'type' => 'html',
            'html' => "<p>Hello</p>\n",
            'hide_display' => true,
            'display' => 'Introduction',
        ], $fieldtype->toFieldArray());
    }
}
