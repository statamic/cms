<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Replicator;
use Tests\TestCase;

class ReplicatorTest extends TestCase
{
    /** @test */
    public function it_preprocesses_the_values()
    {
        RowId::shouldReceive('generate')->twice()->andReturn('random-string-1', 'random-string-2');

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
                    ],
                ],
                'two' => [
                    'fields' => [
                        ['handle' => 'age', 'field' => 'testfieldset.numbers'], // test field reference
                        ['handle' => 'food', 'field' => ['type' => 'text']], // test inline field
                    ],
                ],
            ],
        ]))->setValue([
            [
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                'type' => 'two',
                'age' => '13', // corresponding fieldtype has preprocessing
                'food' => 'pizza', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
            ],
        ]);

        $this->assertSame([
            [
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                '_id' => 'random-string-1',
                'enabled' => true,
            ],
            [
                'type' => 'two',
                'age' => 13,
                'food' => 'pizza',
                'foo' => 'more bar',
                '_id' => 'random-string-2',
                'enabled' => true,
            ],
        ], $field->preProcess()->value());
    }

    /** @test */
    public function it_preprocesses_the_values_recursively()
    {
        RowId::shouldReceive('generate')->twice()->andReturn('random-string-1', 'random-string-2');

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
                                    ],
                                ],
                            ],
                        ]],
                    ],
                ],
            ],
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
                        'nested_foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
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
                        '_id' => 'random-string-1',
                        'enabled' => true,
                    ],
                ],
                '_id' => 'random-string-2',
                'enabled' => true,
            ],
        ], $field->preProcess()->value());
    }

    /** @test */
    public function it_processes_the_values()
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
                    ],
                ],
                'two' => [
                    'fields' => [
                        ['handle' => 'age', 'field' => 'testfieldset.numbers'], // test field reference
                        ['handle' => 'food', 'field' => ['type' => 'text']], // test inline field
                    ],
                ],
            ],
        ]))->setValue([
            [
                '_id' => '1',
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                '_id' => '2',
                'type' => 'two',
                'age' => '13', // corresponding fieldtype has preprocessing
                'food' => 'pizza', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
            ],
        ]);

        $this->assertSame([
            [
                'id' => '1',
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
            ],
            [
                'id' => '2',
                'type' => 'two',
                'age' => 13,
                'food' => 'pizza',
                'foo' => 'more bar',
            ],
        ], $field->process()->value());
    }

    /** @test */
    public function it_processes_the_values_recursively()
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
                                    ],
                                ],
                            ],
                        ]],
                    ],
                ],
            ],
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
                        'nested_foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            [
                'id' => '1',
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_replicator' => [
                    [
                        'id' => '2',
                        'type' => 'two',
                        'nested_age' => 13,
                        'nested_food' => 'pizza',
                        'nested_foo' => 'more bar',
                    ],
                ],
            ],
        ], $field->process()->value());
    }

    /** @test */
    public function it_preloads_preprocessed_default_values()
    {
        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'main' => [
                    'fields' => [
                        ['handle' => 'things', 'field' => ['type' => 'array']],
                    ],
                ],
            ],
        ]));

        $expected = [
            'things' => [],
        ];

        $this->assertEquals($expected, $field->fieldtype()->preload()['defaults']['main']);
    }

    /** @test */
    public function it_preloads_new_meta_with_preprocessed_values()
    {
        RowId::shouldReceive('generate')->twice()->andReturn('random-string-1', 'random-string-2');

        // For this test, use a grid field with min_rows.
        // It doesn't have to be, but it's a fieldtype that would
        // require preprocessed values to be provided down the line.
        // https://github.com/statamic/cms/issues/3481

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'things',
                            'field' => [
                                'type' => 'grid',
                                'min_rows' => 2,
                                'fields' => [
                                    ['handle' => 'one', 'field' => ['type' => 'text']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]));

        $expected = [
            '_' => '_',
            'things' => [ // this array is the preloaded meta for the grid field
                'defaults' => [
                    'one' => null, // default value for the text field
                ],
                'new' => [
                    'one' => null, // meta for the text field
                ],
                'existing' => [
                    'random-string-1' => ['one' => null],
                    'random-string-2' => ['one' => null],
                ],
            ],
        ];

        $this->assertEquals($expected, $field->fieldtype()->preload()['new']['main']);
    }

    /** @test */
    public function it_augments()
    {
        (new class extends Fieldtype
        {
            public static $handle = 'test';

            public function augment($value)
            {
                return $value.' (augmented)';
            }
        })::register();

        $field = new Field('test', [
            'type' => 'replicator',
            'sets' => [
                'a' => [
                    'fields' => [
                        ['handle' => 'words', 'field' => ['type' => 'test']],
                    ],
                ],
            ],
        ]);

        $augmented = $field->fieldtype()->augment([
            ['type' => 'a', 'words' => 'one'],
            ['type' => 'a', 'words' => 'two'],
        ]);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals([
            ['type' => 'a', 'words' => 'one (augmented)'],
            ['type' => 'a', 'words' => 'two (augmented)'],
        ], collect($augmented)->toArray());
    }

    /** @test */
    public function it_converts_a_queryable_value()
    {
        $this->assertNull((new Replicator)->toQueryableValue(null));
        $this->assertNull((new Replicator)->toQueryableValue([]));
        $this->assertEquals([['foo' => 'bar']], (new Replicator)->toQueryableValue([['foo' => 'bar']]));
    }
}
