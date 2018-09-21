<?php

namespace Tests\Fields\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Fieldtypes\NestedFields;
use Facades\Statamic\Fields\FieldtypeRepository;

class NestedFieldsTest extends TestCase
{
    /** @test */
    function it_preprocesses_each_value()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('assets')
            ->andReturn(new class extends Fieldtype {
                protected $configFields = [
                    'max_files' => ['type' => 'integer'],
                    'container' => ['type' => 'plain']
                ];
            });

        FieldtypeRepository::shouldReceive('find')
            ->with('plain')
            ->andReturn(new class extends Fieldtype {
                public function preProcess($data) {
                    return $data;
                }
            });

        FieldtypeRepository::shouldReceive('find')
            ->with('integer')
            ->andReturn(new class extends Fieldtype {
                public function preProcess($data) {
                    return (int) $data;
                }
            });

        $actual = (new NestedFields)->preProcess([
            'image' => [
                'type' => 'assets',
                'max_files' => '2', // corresponding fieldtype has preprocessing
                'container' => 'main', // corresponding fieldtype has no preprocessing
                'foo' => 'bar' // no corresponding fieldtype, so theres no preprocessing
            ]
        ]);

        $this->assertSame([
            [
                'type' => 'assets',
                'max_files' => 2,
                'container' => 'main',
                'foo' => 'bar',
                'handle' => 'image',
            ]
        ], $actual);
    }

    /** @test */
    function it_moves_handles_out_into_the_keys()
    {
        $actual = (new NestedFields)->process([
            ['handle' => 'one', 'type' => 'text'],
            ['handle' => 'two', 'type' => 'text']
        ]);

        $this->assertSame([
            'one' => ['type' => 'text'],
            'two' => ['type' => 'text']
        ], $actual);
    }

    /** @test */
    function it_removes_100_percent_widths_when_processing()
    {
        $actual = (new NestedFields)->process([
            ['handle' => 'one', 'type' => 'text', 'width' => 100],
            ['handle' => 'two', 'type' => 'text', 'width' => 50]
        ]);

        $this->assertSame([
            'one' => ['type' => 'text'],
            'two' => ['type' => 'text', 'width' => 50]
        ], $actual);
    }

    /** @test */
    function it_removes_ids_when_processing()
    {
        $actual = (new NestedFields)->process([
            ['handle' => 'one', 'type' => 'text', '_id' => '1'],
            ['handle' => 'two', 'type' => 'text', '_id' => '2']
        ]);

        $this->assertSame([
            'one' => ['type' => 'text'],
            'two' => ['type' => 'text']
        ], $actual);
    }

    /** @test */
    function it_removes_nulls_and_empty_strings_when_processing()
    {
        $actual = (new NestedFields)->process([
            [
                'handle' => 'one',
                'type' => 'text',
                'foo' => '',
                'bar' => null,
                'baz' => 'qux',
                'arr' => [
                    'foo' => '',
                    'bar' => null,
                    'baz' => 'qux'
                ]
            ],
        ]);

        $this->assertSame([
            'one' => [
                'type' => 'text',
                'baz' => 'qux',
                'arr' => [
                    'baz' => 'qux'
                ]
            ],
        ], $actual);
    }

    /** @test */
    function it_processes_each_value()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('assets')
            ->andReturn(new class extends Fieldtype {
                protected $configFields = [
                    'max_files' => ['type' => 'integer']
                ];
            });

        FieldtypeRepository::shouldReceive('find')
            ->with('integer')
            ->andReturn(new class extends Fieldtype {
                public function process($data) {
                    return (int) $data;
                }
            });

        FieldtypeRepository::shouldReceive('find')
            ->with('plain')
            ->andReturn(new class extends Fieldtype {
                public function preProcess($data) {
                    return $data;
                }
            });

        $actual = (new NestedFields)->process([
            [
                '_id' => 'id-1',
                'handle' => 'one',
                'type' => 'plain',
                'instructions' => null,
                'width' => 100,
                'display' => 'First Field'
            ],
            [
                '_id' => 'id-2',
                'handle' => 'two',
                'type' => 'assets',
                'instructions' => 'Some instructions',
                'width' => 50,
                'display' => 'Second Field',
                'max_files' => '2',
            ],
        ]);

        $this->assertSame([
            'one' => [
                'type' => 'plain',
                'display' => 'First Field'
            ],
            'two' => [
                'type' => 'assets',
                'instructions' => 'Some instructions',
                'width' => 50,
                'display' => 'Second Field',
                'max_files' => 2, // The `assets` fieldtype's `max_files` is an `integer` fieldtype, which converts to an integer.
            ]
        ], $actual);
    }
}
