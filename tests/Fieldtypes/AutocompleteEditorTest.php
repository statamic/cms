<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
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

        $this->assertSame('Hi {{ first_name }}, thanks!', $fieldtype->process('Hi {{ first_name }}, thanks!'));
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

    private function fieldtype($config = [])
    {
        return (new AutocompleteEditor)->setField(new Field('test', array_merge(['type' => 'autocomplete_editor'], $config)));
    }
}
