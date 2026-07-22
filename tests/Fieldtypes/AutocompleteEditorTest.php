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
        $this->assertNull($fieldtype->process([]));
    }

    #[Test]
    public function it_processes_a_lone_empty_paragraph_to_null_in_block_mode()
    {
        $fieldtype = $this->fieldtype();

        $this->assertNull($fieldtype->process([['type' => 'paragraph']]));
    }

    #[Test]
    public function it_processes_block_content_unchanged()
    {
        $fieldtype = $this->fieldtype();

        $value = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello'],
                ],
            ],
        ];

        $this->assertSame($value, $fieldtype->process($value));
    }

    #[Test]
    public function it_preprocesses_block_content_unchanged()
    {
        $fieldtype = $this->fieldtype();

        $value = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello'],
                ],
            ],
        ];

        $this->assertSame($value, $fieldtype->preProcess($value));
    }

    #[Test]
    public function it_round_trips_block_content()
    {
        $fieldtype = $this->fieldtype();

        $value = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello'],
                ],
            ],
        ];

        $this->assertSame($value, $fieldtype->preProcess($fieldtype->process($value)));
    }

    #[Test]
    public function it_unwraps_a_single_paragraph_when_processing_inline_content()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $value = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello'],
                ],
            ],
        ];

        $this->assertSame(
            [['type' => 'text', 'text' => 'Hello']],
            $fieldtype->process($value)
        );
    }

    #[Test]
    public function it_processes_an_empty_inline_paragraph_to_null()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $this->assertNull($fieldtype->process([['type' => 'paragraph']]));
    }

    #[Test]
    public function it_wraps_flat_inline_content_in_a_paragraph_when_preprocessing()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $stored = [['type' => 'text', 'text' => 'Hello']];

        $this->assertSame(
            [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello'],
                    ],
                ],
            ],
            $fieldtype->preProcess($stored)
        );
    }

    #[Test]
    public function it_round_trips_inline_content()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $stored = [['type' => 'text', 'text' => 'Hello']];

        $this->assertSame(
            $stored,
            $fieldtype->process($fieldtype->preProcess($stored))
        );
    }

    #[Test]
    public function preprocess_removes_nodes_missing_a_type()
    {
        $fieldtype = $this->fieldtype();

        $value = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello'],
                ],
            ],
            ['no' => 'type'],
        ];

        $this->assertSame(
            [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello'],
                    ],
                ],
            ],
            $fieldtype->preProcess($value)
        );
    }

    #[Test]
    public function preprocess_returns_empty_array_for_empty_value()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame([], $fieldtype->preProcess([]));
        $this->assertSame([], $fieldtype->preProcess(null));
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
