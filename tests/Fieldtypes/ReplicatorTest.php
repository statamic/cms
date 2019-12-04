<?php

namespace Tests\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Grid;
use Statamic\Fieldtypes\NestedFields;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldtypeRepository;

class ReplicatorTest extends TestCase
{
    /** @test */
    function it_preprocesses_the_values()
    {
        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'one' => [
                    'fields' => [
                        ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                        ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
                    ]
                ],
                'two' => [
                    'fields' => [
                        ['handle' => 'age', 'field' => 'testfieldset.numbers'], // test field reference
                        ['handle' => 'food', 'field' => ['type' => 'text']], // test inline field
                    ]
                ]
            ]
        ]))->setValue([
            [
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar' // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                'type' => 'two',
                'age' => '13', // corresponding fieldtype has preprocessing
                'food' => 'pizza', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar' // no corresponding fieldtype, so theres no preprocessing
            ]
        ]);

        $this->assertSame([
            [
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                '_id' => 'set-0',
                'enabled' => true,
            ],
            [
                'type' => 'two',
                'age' => 13,
                'food' => 'pizza',
                'foo' => 'more bar',
                '_id' => 'set-1',
                'enabled' => true,
            ]
        ], $field->preProcess()->value());
    }

    /** @test */
    function it_preprocesses_the_values_recursively()
    {
        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'one' => [
                    'fields' => [
                        ['handle' => 'numbers', 'field' => 'testfieldset.numbers'],
                        ['handle' => 'words', 'field' => ['type' => 'text']],
                        ['handle' => 'nested_replicator', 'field' => [
                            'type' => 'replicator',
                            'sets' => [
                                'two' => [
                                    'fields' => [
                                        ['handle' => 'nested_age', 'field' => 'testfieldset.numbers'],
                                        ['handle' => 'nested_food', 'field' => ['type' => 'text']],
                                    ]
                                ]
                            ]
                        ]]
                    ]
                ]
            ]
        ]))->setValue([
            [
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_replicator' => [
                    [
                        'type' => 'two',
                        'nested_age' => '13', // corresponding fieldtype has preprocessing
                        'nested_food' => 'pizza', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more bar' // no corresponding fieldtype, so theres no preprocessing
                    ]
                ]
            ],
        ]);

        $this->assertSame([
            [
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_replicator' => [
                    [
                        'type' => 'two',
                        'nested_age' => 13,
                        'nested_food' => 'pizza',
                        'nested_foo' => 'more bar',
                        '_id' => 'set-0',
                        'enabled' => true,
                    ]
                ],
                '_id' => 'set-0',
                'enabled' => true,
            ]
        ], $field->preProcess()->value());
    }

    /** @test */
    function it_processes_the_values()
    {
        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'one' => [
                    'fields' => [
                        ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                        ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
                    ]
                ],
                'two' => [
                    'fields' => [
                        ['handle' => 'age', 'field' => 'testfieldset.numbers'], // test field reference
                        ['handle' => 'food', 'field' => ['type' => 'text']], // test inline field
                    ]
                ]
            ]
        ]))->setValue([
            [
                '_id' => '1',
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar' // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                '_id' => '2',
                'type' => 'two',
                'age' => '13', // corresponding fieldtype has preprocessing
                'food' => 'pizza', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar' // no corresponding fieldtype, so theres no preprocessing
            ]
        ]);

        $this->assertSame([
            [
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
            ],
            [
                'type' => 'two',
                'age' => 13,
                'food' => 'pizza',
                'foo' => 'more bar',
            ]
        ], $field->process()->value());
    }

    /** @test */
    function it_processes_the_values_recursively()
    {
        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'one' => [
                    'fields' => [
                        ['handle' => 'numbers', 'field' => 'testfieldset.numbers'],
                        ['handle' => 'words', 'field' => ['type' => 'text']],
                        ['handle' => 'nested_replicator', 'field' => [
                            'type' => 'replicator',
                            'sets' => [
                                'two' => [
                                    'fields' => [
                                        ['handle' => 'nested_age', 'field' => 'testfieldset.numbers'],
                                        ['handle' => 'nested_food', 'field' => ['type' => 'text']],
                                    ]
                                ]
                            ]
                        ]]
                    ]
                ]
            ]
        ]))->setValue([
            [
                '_id' => '1',
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_replicator' => [
                    [
                        '_id' => '2',
                        'type' => 'two',
                        'nested_age' => '13', // corresponding fieldtype has preprocessing
                        'nested_food' => 'pizza', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more bar' // no corresponding fieldtype, so theres no preprocessing
                    ]
                ]
            ],
        ]);

        $this->assertSame([
            [
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_replicator' => [
                    [
                        'type' => 'two',
                        'nested_age' => 13,
                        'nested_food' => 'pizza',
                        'nested_foo' => 'more bar',
                    ]
                ]
            ]
        ], $field->process()->value());
    }
}




