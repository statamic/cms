<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldRepository;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Replicator;
use Statamic\Fieldtypes\RowId;
use Tests\TestCase;

class ReplicatorTest extends TestCase
{
    #[Test]
    #[DataProvider('groupedSetsProvider')]
    public function it_preprocesses_with_empty_value($areSetsGrouped)
    {
        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => $this->groupSets($areSetsGrouped, [
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
            ]),
        ]));

        $this->assertSame([], $field->preProcess()->value());
    }

    #[Test]
    #[DataProvider('groupedSetsProvider')]
    public function it_preprocesses_the_values($areSetsGrouped)
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->twice()->andReturn('random-string-1', 'random-string-2');
        });

        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => $this->groupSets($areSetsGrouped, [
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
            ]),
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

    #[Test]
    #[DataProvider('groupedSetsProvider')]
    public function it_preprocesses_the_values_recursively($areSetsGrouped)
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->twice()->andReturn('random-string-1', 'random-string-2');
        });

        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => $this->groupSets($areSetsGrouped, [
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
            ]),
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_processes_the_values_recursively_with_a_custom_id()
    {
        config()->set('statamic.system.row_id_handle', '_id');

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
                '_id' => 'set-id-1',
                'id' => 'user-input-id-1',
                'type' => 'one',
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_replicator' => [
                    [
                        '_id' => 'set-id-2',
                        'id' => 'user-input-id-2',
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
                '_id' => 'set-id-1',
                'id' => 'user-input-id-1',
                'type' => 'one',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_replicator' => [
                    [
                        '_id' => 'set-id-2',
                        'id' => 'user-input-id-2',
                        'type' => 'two',
                        'nested_age' => 13,
                        'nested_food' => 'pizza',
                        'nested_foo' => 'more bar',
                    ],
                ],
            ],
        ], $field->process()->value());
    }

    #[Test]
    #[DataProvider('groupedSetsProvider')]
    public function it_preloads($areSetsGrouped)
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn(
                'random-string-1',
                'random-string-2',
                'random-string-3',
                'random-string-4',
                'random-string-5',
                'random-string-6',
            );
        });

        // For this test, use a grid field with min_rows.
        // It doesn't have to be, but it's a fieldtype that would
        // require preprocessed values to be provided down the line.
        // https://github.com/statamic/cms/issues/3481

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => $this->groupSets($areSetsGrouped, [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'a_text_field',
                            'field' => [
                                'type' => 'text',
                                'default' => 'the default',
                            ],
                        ],
                        [
                            'handle' => 'a_grid_field',
                            'field' => [
                                'type' => 'grid',
                                'min_rows' => 2,
                                'fields' => [
                                    ['handle' => 'one', 'field' => ['type' => 'text', 'default' => 'default in nested']],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]))->setValue([
            [
                'type' => 'main',
                'a_text_field' => 'hello',
                'a_grid_field' => [
                    ['one' => 'foo'],
                    ['one' => 'bar'],
                ],
            ],
            [
                // Ensure that if there's a set that isn't configured, it gets left as-is and doesn't
                // throw any errors. For example, if a set is removed from the config.
                'type' => 'nope',
                'foo' => 'bar',
            ],
        ]);

        // The preload method expects the field to already be preprocessed.
        // This will add stuff like set/row IDs to *existing* values, but not
        // to any "new" or "default" values. That'll be handled by the fieldtype.
        // During this preprocess step, the 2 nested grid rows, and the 1 replicator
        // set will be assigned the first 3 random-string-n IDs.
        $field = $field->preProcess();

        $meta = $field->fieldtype()->preload();

        // Assert about the "existing" sub-array.
        // This is meta data for subfields of existing sets.
        $this->assertCount(2, $meta['existing']);

        // The set IDs assigned during preprocess.
        $this->assertArrayHasKey('random-string-3', $meta['existing']);
        $this->assertArrayHasKey('random-string-4', $meta['existing']);

        $this->assertEquals([
            '_' => '_', // An empty key to enforce an object in JavaScript.
            'a_text_field' => null, // the text field doesn't have meta data.
            'a_grid_field' => [ // this array is the preloaded meta for the grid field
                'defaults' => [
                    'one' => 'default in nested', // default value for the text field
                ],
                'new' => [
                    'one' => null, // meta for the text field
                ],
                'existing' => [
                    'random-string-1' => ['one' => null],
                    'random-string-2' => ['one' => null],
                ],
            ],
        ], $meta['existing']['random-string-3']);

        $this->assertEquals([
            '_' => '_', // An empty key to enforce an object in JavaScript.
            // The "foo" key doesn't appear here since there's no corresponding "nope" set config.
        ], $meta['existing']['random-string-4']);

        // Assert about the "defaults" sub-array.
        // These are the initial values used for subfields when a new set is added.
        $this->assertCount(1, $meta['defaults']);
        $this->assertArrayHasKey('main', $meta['defaults']);
        $this->assertEquals([
            'a_text_field' => 'the default',
            'a_grid_field' => [
                ['_id' => 'random-string-5', 'one' => 'default in nested'],
                ['_id' => 'random-string-6', 'one' => 'default in nested'],
            ],
        ], $meta['defaults']['main']);

        // Assert about the "new" sub-array.
        // This is meta data for subfields when a new set is added.
        $this->assertCount(1, $meta['new']);
        $this->assertArrayHasKey('main', $meta['new']);
        $this->assertEquals([
            '_' => '_', // An empty key to enforce an object in JavaScript.
            'a_text_field' => null, // the text field doesn't have meta data.
            'a_grid_field' => [ // this array is the preloaded meta for the grid field
                'defaults' => [
                    'one' => 'default in nested', // default value for the text field
                ],
                'new' => [
                    'one' => null, // meta for the text field
                ],
                'existing' => [
                    'random-string-5' => ['one' => null],
                    'random-string-6' => ['one' => null],
                ],
            ],
        ], $meta['new']['main']);
    }

    #[Test]
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
            ['id' => '1', 'type' => 'a', 'words' => 'one'],
            ['type' => 'a', 'words' => 'two'], // id intentionally omitted
        ]);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals([
            ['id' => '1', 'type' => 'a', 'words' => 'one (augmented)'],
            ['id' => null, 'type' => 'a', 'words' => 'two (augmented)'],
        ], collect($augmented)->toArray());
    }

    #[Test]
    public function it_augments_with_custom_row_id_handle()
    {
        config(['statamic.system.row_id_handle' => '_id']);

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
                        ['handle' => 'id', 'field' => ['type' => 'test']],
                    ],
                ],
            ],
        ]);

        $augmented = $field->fieldtype()->augment([
            ['_id' => '1', 'id' => '7', 'type' => 'a', 'words' => 'one'],
            ['type' => 'a', 'id' => '8', 'words' => 'two'], // row id intentionally omitted
            ['_id' => '3', 'type' => 'a', 'words' => 'three'], // id field intentionally omitted
        ]);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals([
            ['_id' => '1', 'id' => '7 (augmented)', 'type' => 'a', 'words' => 'one (augmented)'],
            ['_id' => null, 'id' => '8 (augmented)', 'type' => 'a', 'words' => 'two (augmented)'],
            ['_id' => '3', 'id' => ' (augmented)', 'type' => 'a', 'words' => 'three (augmented)'],
        ], collect($augmented)->toArray());
    }

    #[Test]
    public function it_converts_a_queryable_value()
    {
        $this->assertNull((new Replicator)->toQueryableValue(null));
        $this->assertNull((new Replicator)->toQueryableValue([]));
        $this->assertEquals([['foo' => 'bar']], (new Replicator)->toQueryableValue([['foo' => 'bar']]));
    }

    #[Test]
    #[DataProvider('groupedSetsProvider')]
    public function it_generates_field_path_prefix($areSetsGrouped)
    {
        $fieldtype = new class extends Fieldtype
        {
            public static function handle()
            {
                return 'custom';
            }

            public function preProcess($value)
            {
                return $this->field()->fieldPathPrefix();
            }

            public function process($value)
            {
                return $this->field()->fieldPathPrefix();
            }

            public function preload()
            {
                return ['fieldPathPrefix' => $this->field()->fieldPathPrefix()];
            }

            public function augment($value)
            {
                return $this->field()->fieldPathPrefix();
            }
        };

        $fieldtype::register();

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => $this->groupSets($areSetsGrouped, [
                'one' => [
                    'fields' => [
                        ['handle' => 'words', 'field' => ['type' => 'custom']],
                    ],
                ],
            ]),
        ]))->setValue([
            [
                '_id' => 'set-id-1',
                'type' => 'one',
                'words' => 'test',
            ],
            [
                '_id' => 'set-id-2',
                'type' => 'one',
                'words' => 'test',
            ],
        ]);

        $value = $field->augment()->value()->value();
        $this->assertEquals('test.0.words', $value[0]['words']);
        $this->assertEquals('test.1.words', $value[1]['words']);

        $value = $field->preProcess()->value();
        $this->assertEquals('test.0.words', $value[0]['words']);
        $this->assertEquals('test.1.words', $value[1]['words']);

        $value = $field->process()->value();
        $this->assertEquals('test.0.words', $value[0]['words']);
        $this->assertEquals('test.1.words', $value[1]['words']);

        $value = $field->fieldtype()->preload();
        $this->assertEquals('test.0.words', $value['existing']['set-id-1']['words']['fieldPathPrefix']);
        $this->assertEquals('test.1.words', $value['existing']['set-id-2']['words']['fieldPathPrefix']);
        $this->assertEquals('test.-1.words', $value['new']['one']['words']['fieldPathPrefix']);
        $this->assertEquals('test.-1.words', $value['defaults']['one']['words']);
    }

    #[Test]
    #[DataProvider('groupedSetsProvider')]
    public function it_generates_nested_field_path_prefix($areSetsGrouped)
    {
        $fieldtype = new class extends Fieldtype
        {
            public static function handle()
            {
                return 'custom';
            }

            public function preProcess($value)
            {
                return $this->field()->fieldPathPrefix();
            }

            public function process($value)
            {
                return $this->field()->fieldPathPrefix();
            }

            public function preload()
            {
                return ['fieldPathPrefix' => $this->field()->fieldPathPrefix()];
            }

            public function augment($value)
            {
                return $this->field()->fieldPathPrefix();
            }
        };

        $fieldtype::register();

        $field = (new Field('test', [
            'type' => 'replicator',
            'sets' => $this->groupSets($areSetsGrouped, [
                'one' => [
                    'fields' => [
                        ['handle' => 'nested', 'field' => [
                            'type' => 'replicator',
                            'sets' => $this->groupSets($areSetsGrouped, [
                                'two' => [
                                    'fields' => [
                                        ['handle' => 'words', 'field' => ['type' => 'custom']],
                                    ],
                                ],
                            ]),
                        ]],
                    ],
                ],
            ]),
        ]))->setValue([
            [
                '_id' => 'set-id-1',
                'type' => 'one',
                'nested' => [
                    [
                        '_id' => 'nested-set-id-1a',
                        'type' => 'two',
                        'words' => 'test',
                    ],
                    [
                        '_id' => 'nested-set-id-1b',
                        'type' => 'two',
                        'words' => 'test',
                    ],
                ],
            ],
            [
                '_id' => 'set-id-2',
                'type' => 'one',
                'nested' => [
                    [
                        '_id' => 'nested-set-id-2a',
                        'type' => 'two',
                        'words' => 'test',
                    ],
                    [
                        '_id' => 'nested-set-id-2b',
                        'type' => 'two',
                        'words' => 'test',
                    ],
                ],
            ],
        ]);

        $value = $field->augment()->value()->value();
        $this->assertEquals('test.0.nested.0.words', $value[0]['nested'][0]['words']);
        $this->assertEquals('test.0.nested.1.words', $value[0]['nested'][1]['words']);
        $this->assertEquals('test.1.nested.0.words', $value[1]['nested'][0]['words']);
        $this->assertEquals('test.1.nested.1.words', $value[1]['nested'][1]['words']);
    }

    public static function groupedSetsProvider()
    {
        return [
            'grouped sets (new)' => [true],
            'ungrouped sets (old)' => [false],
        ];
    }

    private function groupSets($shouldGroup, $sets)
    {
        if (! $shouldGroup) {
            return $sets;
        }

        return [
            'group_one' => ['sets' => $sets],
        ];
    }
}
