<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\AutocompleteEditor;
use Tests\TestCase;

class AutocompleteEditorTest extends TestCase
{
    #[Test]
    public function it_is_not_selectable()
    {
        $this->assertFalse((new AutocompleteEditor)->selectable());
    }

    #[Test]
    public function it_has_expected_config_defaults()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('@', $fieldtype->config('trigger'));
        $this->assertNull($fieldtype->config('placeholder'));
        $this->assertFalse($fieldtype->config('inline'));
        $this->assertFalse($fieldtype->config('enable_line_breaks'));
        $this->assertSame(
            ['bold', 'italic', 'h2', 'h3', 'bulletlist', 'orderedlist'],
            $fieldtype->config('buttons')
        );
    }

    #[Test]
    public function config_values_can_be_overridden()
    {
        $fieldtype = $this->fieldtype([
            'trigger' => '#',
            'inline' => true,
            'enable_line_breaks' => true,
            'buttons' => ['bold'],
        ]);

        $this->assertSame('#', $fieldtype->config('trigger'));
        $this->assertTrue($fieldtype->config('inline'));
        $this->assertTrue($fieldtype->config('enable_line_breaks'));
        $this->assertSame(['bold'], $fieldtype->config('buttons'));
    }

    #[Test]
    public function it_processes_null_and_empty_values_to_null()
    {
        $fieldtype = $this->fieldtype();

        $this->assertNull($fieldtype->process(null));
        $this->assertNull($fieldtype->process(''));
    }

    #[Test]
    public function it_processes_a_markdown_string_unchanged()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('Hi [[ first_name ]], thanks!', $fieldtype->process('Hi [[ first_name ]], thanks!'));
    }

    #[Test]
    public function preload_normalizes_a_list_of_plain_string_options()
    {
        $fieldtype = $this->fieldtype([
            'options' => ['red', 'green'],
        ]);

        $this->assertSame(
            [
                ['value' => 'red', 'label' => 'red'],
                ['value' => 'green', 'label' => 'green'],
            ],
            $fieldtype->preload()['options']
        );
    }

    #[Test]
    public function preload_normalizes_an_associative_array_of_options()
    {
        $fieldtype = $this->fieldtype([
            'options' => ['red' => 'Red', 'green' => 'Green'],
        ]);

        $this->assertSame(
            [
                ['value' => 'red', 'label' => 'Red'],
                ['value' => 'green', 'label' => 'Green'],
            ],
            $fieldtype->preload()['options']
        );
    }

    #[Test]
    public function preload_normalizes_key_value_rows_from_the_array_fieldtype()
    {
        $fieldtype = $this->fieldtype([
            'options' => [
                ['key' => 'red', 'value' => 'Red'],
                ['key' => 'green', 'value' => 'Green'],
            ],
        ]);

        $this->assertSame(
            [
                ['value' => 'red', 'label' => 'Red'],
                ['value' => 'green', 'label' => 'Green'],
            ],
            $fieldtype->preload()['options']
        );
    }

    #[Test]
    public function preload_returns_an_empty_options_array_when_none_are_configured()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame([], $fieldtype->preload()['options']);
    }

    #[Test]
    public function it_augments_markdown_to_html()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame(
            "<p>Hi <strong>John</strong></p>\n",
            $fieldtype->augment('Hi **John**')
        );
    }

    #[Test]
    public function it_augments_multiple_blocks_of_markdown()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame(
            "<p>One</p>\n<p>Two</p>\n",
            $fieldtype->augment("One\n\nTwo")
        );
    }

    #[Test]
    public function it_unwraps_the_paragraph_when_inline()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $this->assertSame('Hi <strong>John</strong>', $fieldtype->augment('Hi **John**'));
    }

    #[Test]
    public function it_leaves_multiple_paragraphs_wrapped_when_inline()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $this->assertSame("<p>One</p>\n<p>Two</p>", $fieldtype->augment("One\n\nTwo"));
    }

    #[Test]
    public function it_augments_null_and_empty_values_to_null()
    {
        $fieldtype = $this->fieldtype();

        $this->assertNull($fieldtype->augment(null));
        $this->assertNull($fieldtype->augment(''));
    }

    #[Test]
    public function augment_delegates_to_the_parse_markdown_method()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame($fieldtype->parseMarkdown('Hi **John**'), $fieldtype->augment('Hi **John**'));
    }

    #[Test]
    public function it_leaves_mention_tokens_alone_when_augmenting()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame("<p>Hi [[ first_name ]]</p>\n", $fieldtype->augment('Hi [[ first_name ]]'));
    }

    #[Test]
    public function antlers_is_parsed_after_the_markdown_is_augmented()
    {
        $fieldtype = $this->fieldtype(['antlers' => true, 'inline' => true]);

        $value = new Value('Hi **{{ first_name }}**', 'test', $fieldtype);

        $this->assertSame(
            'Hi <strong>John</strong>',
            (string) Antlers::parse('{{ test }}', ['test' => $value, 'first_name' => 'John'])
        );
    }

    #[Test]
    public function antlers_is_not_parsed_when_the_config_is_disabled()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $value = new Value('Hi {{ first_name }}', 'test', $fieldtype);

        $this->assertSame(
            'Hi {{ first_name }}',
            (string) Antlers::parse('{{ test }}', ['test' => $value, 'first_name' => 'John'])
        );
    }

    private function fieldtype($config = [])
    {
        return (new AutocompleteEditor)->setField(new Field('test', array_merge(['type' => 'autocomplete_editor'], $config)));
    }
}
