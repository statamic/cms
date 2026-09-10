<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldRepository;
use Facades\Tests\Factories\EntryFactory;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Replicator;
use Statamic\Fieldtypes\RowId;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReplicatorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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

    #[Test]
    public function it_can_return_set_defaults()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn('random-string-1', 'random-string-2');
        });

        $fieldset = Fieldset::make('foreign_fields')->setContents(['fields' => [
            ['handle' => 'an_imported_field', 'field' => ['type' => 'text', 'default' => 'default from foreign field']],
        ]]);
        Fieldset::shouldReceive('find')->with('foreign_fields')->andReturn($fieldset);

        $blueprint = Facades\Blueprint::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'content',
                            'field' => [
                                'type' => 'replicator',
                                'sets' => [
                                    'main' => [
                                        'sets' => [
                                            'text' => [
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
                                                    [
                                                        'import' => 'foreign_fields',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Facades\Blueprint::partialMock();
        Facades\Blueprint::shouldReceive('find')->with('collections.pages.default')->andReturn($blueprint);

        $user = tap(Facades\User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]),
                'field' => 'content',
                'set' => 'text',
            ])
            ->assertOk();

        $this->assertEquals([
            'a_text_field' => 'the default',
            'a_grid_field' => [
                ['_id' => 'random-string-1', 'one' => 'default in nested'],
                ['_id' => 'random-string-2', 'one' => 'default in nested'],
            ],
            'an_imported_field' => 'default from foreign field',
        ], $response->json('defaults'));

        $this->assertEquals([
            '_' => '_',
            'a_text_field' => null,
            'a_grid_field' => [
                'defaults' => [
                    'one' => 'default in nested',
                ],
                'new' => [
                    'one' => null,
                ],
                'existing' => [
                    'random-string-1' => ['one' => null],
                    'random-string-2' => ['one' => null],
                ],
            ],
            'an_imported_field' => null,
        ], $response->json('new'));
    }

    /**
     * We're purposefully naming the sets the same as its nested field to replicate the reported issue.
     *
     * @see https://github.com/statamic/cms/issues/13687
     */
    #[Test]
    public function it_can_return_set_defaults_for_nested_sets()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn('random-string-1', 'random-string-2');
        });

        $cards = Fieldset::make('cards')->setContents(['fields' => [
            ['handle' => 'cards', 'field' => ['type' => 'replicator', 'sets' => [
                'card' => [
                    'sets' => [
                        'card' => [
                            'fields' => [
                                ['handle' => 'text_field', 'field' => ['type' => 'text', 'default' => 'the default']],
                            ],
                        ],
                    ],
                ],
            ]]],
        ]]);

        $article = Fieldset::make('article')->setContents(['fields' => [
            ['handle' => 'bard_field', 'field' => ['type' => 'bard', 'sets' => [
                'cards' => [
                    'sets' => [
                        'cards' => [
                            'fields' => [
                                ['import' => 'cards'],
                            ],
                        ],
                    ],
                ],
            ]]],
        ]]);

        $pageBuilder = Fieldset::make('page_builder')->setContents(['fields' => [
            ['handle' => 'page_builder', 'field' => ['type' => 'replicator', 'sets' => [
                'replicator_set_group' => [
                    'sets' => [
                        'article' => [
                            'fields' => [
                                ['handle' => 'text_field', 'field' => ['type' => 'text', 'default' => 'the default']],
                                ['import' => 'article'],
                            ],
                        ],
                    ],
                ],
            ]]],
        ]]);

        Fieldset::shouldReceive('find')->with('cards')->andReturn($cards);
        Fieldset::shouldReceive('find')->with('article')->andReturn($article);
        Fieldset::shouldReceive('find')->with('page_builder')->andReturn($pageBuilder);

        $blueprint = Facades\Blueprint::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        ['import' => 'page_builder'],
                    ],
                ],
            ],
        ]);

        Facades\Blueprint::partialMock();
        Facades\Blueprint::shouldReceive('find')->with('collections.pages.default')->andReturn($blueprint);

        $user = tap(Facades\User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]),
                'field' => 'page_builder.article.bard_field.cards.cards',
                'set' => 'card',
            ])
            ->assertOk();

        $this->assertEquals([
            'text_field' => 'the default',
        ], $response->json('defaults'));

        $this->assertEquals([
            '_' => '_',
            'text_field' => null,
        ], $response->json('new'));
    }

    /**
     * We're purposefully naming the sets the same as its nested field to replicate the reported issue.
     *
     * @see https://github.com/statamic/cms/issues/13714
     */
    #[Test]
    public function it_can_return_set_defaults_for_replicator_inside_group()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn('random-string-1', 'random-string-2');
        });

        $pageBuilder = Fieldset::make('page_builder')->setContents(['fields' => [
            ['handle' => 'page_builder', 'field' => ['type' => 'replicator', 'sets' => [
                'replicator_set_group' => [
                    'sets' => [
                        'cards_slider' => [
                            'fields' => [
                                [
                                    'handle' => 'cards_slider',
                                    'field' => [
                                        'type' => 'group',
                                        'fields' => [
                                            [
                                                'handle' => 'slider',
                                                'field' => [
                                                    'type' => 'group',
                                                    'fields' => [
                                                        [
                                                            'handle' => 'cards',
                                                            'field' => [
                                                                'type' => 'replicator',
                                                                'sets' => [
                                                                    'replicator_set_group' => [
                                                                        'sets' => [
                                                                            'card' => [
                                                                                'fields' => [
                                                                                    ['handle' => 'card_content', 'field' => ['type' => 'text', 'default' => 'the default']],
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]]],
        ]]);

        Fieldset::shouldReceive('find')->with('page_builder')->andReturn($pageBuilder);

        $blueprint = Facades\Blueprint::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        ['import' => 'page_builder'],
                    ],
                ],
            ],
        ]);

        Facades\Blueprint::partialMock();
        Facades\Blueprint::shouldReceive('find')->with('collections.pages.default')->andReturn($blueprint);

        $user = tap(Facades\User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]),
                'field' => 'page_builder.cards_slider.cards_slider.slider.cards',
                'set' => 'card',
            ])
            ->assertOk();

        $this->assertEquals([
            'card_content' => 'the default',
        ], $response->json('defaults'));

        $this->assertEquals([
            '_' => '_',
            'card_content' => null,
        ], $response->json('new'));
    }

    #[Test]
    public function it_can_return_set_defaults_for_replicator_inside_grid()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn('random-string-1', 'random-string-2');
        });

        $pageBuilder = Fieldset::make('page_builder')->setContents(['fields' => [
            ['handle' => 'page_builder', 'field' => ['type' => 'replicator', 'sets' => [
                'replicator_set_group' => [
                    'sets' => [
                        'cards_slider' => [
                            'fields' => [
                                [
                                    'handle' => 'cards_slider',
                                    'field' => [
                                        'type' => 'grid',
                                        'fields' => [
                                            [
                                                'handle' => 'cards',
                                                'field' => [
                                                    'type' => 'replicator',
                                                    'sets' => [
                                                        'replicator_set_group' => [
                                                            'sets' => [
                                                                'card' => [
                                                                    'fields' => [
                                                                        ['handle' => 'card_content', 'field' => ['type' => 'text', 'default' => 'the default']],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]]],
        ]]);

        Fieldset::shouldReceive('find')->with('page_builder')->andReturn($pageBuilder);

        $blueprint = Facades\Blueprint::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        ['import' => 'page_builder'],
                    ],
                ],
            ],
        ]);

        Facades\Blueprint::partialMock();
        Facades\Blueprint::shouldReceive('find')->with('collections.pages.default')->andReturn($blueprint);

        $user = tap(Facades\User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]),
                'field' => 'page_builder.cards_slider.cards_slider.cards',
                'set' => 'card',
            ])
            ->assertOk();

        $this->assertEquals([
            'card_content' => 'the default',
        ], $response->json('defaults'));

        $this->assertEquals([
            '_' => '_',
            'card_content' => null,
        ], $response->json('new'));
    }

    #[Test]
    public function it_can_return_set_defaults_for_replicator_inside_custom_fieldtype()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn('random-string-1', 'random-string-2');
        });

        $blueprint = Facades\Blueprint::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'stuff',
                            'field' => [
                                'type' => 'custom_fieldtype',
                                'fields' => [
                                    [
                                        'handle' => 'content_blocks',
                                        'field' => [
                                            'type' => 'replicator',
                                            'sets' => [
                                                'text' => [
                                                    'fields' => [
                                                        [
                                                            'handle' => 'body',
                                                            'field' => [
                                                                'type' => 'textarea',
                                                                'default' => 'the default',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Facades\Blueprint::partialMock();
        Facades\Blueprint::shouldReceive('find')->with('collections.pages.default')->andReturn($blueprint);

        $response = $this
            ->actingAs(tap(Facades\User::make()->makeSuper())->save())
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'blueprint' => 'collections.pages.default',
                'field' => 'stuff.content_blocks',
                'set' => 'text',
            ])
            ->assertOk();

        $this->assertEquals([
            'body' => 'the default',
        ], $response->json('defaults'));

        $this->assertEquals([
            '_' => '_',
            'body' => null,
        ], $response->json('new'));
    }

    #[Test]
    public function it_can_return_set_defaults_when_sets_are_stored_in_legacy_format()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->andReturn('random-string-1', 'random-string-2');
        });

        $blueprint = Facades\Blueprint::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'content',
                            'field' => [
                                'type' => 'replicator',
                                'sets' => [
                                    'video' => [
                                        'fields' => [
                                            ['handle' => 'video_url', 'field' => ['type' => 'text', 'default' => 'https://youtu.be/dQw4w9WgXcQ']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Facades\Blueprint::partialMock();
        Facades\Blueprint::shouldReceive('find')->with('collections.pages.default')->andReturn($blueprint);

        $user = tap(Facades\User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]),
                'field' => 'content',
                'set' => 'video',
            ])
            ->assertOk();

        $this->assertEquals([
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ], $response->json('defaults'));

        $this->assertEquals([
            '_' => '_',
            'video_url' => null,
        ], $response->json('new'));
    }

    #[Test]
    public function fields_blink_cache_key_is_site_aware()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        tap(Facades\Collection::make('pages')->routes('/{slug}')->sites(['en', 'fr']))->save();

        $enEntry = EntryFactory::collection('pages')->slug('home')->locale('en')->create();
        $frEntry = EntryFactory::collection('pages')->slug('accueil')->locale('fr')->create();

        $fieldConfig = [
            'type' => 'replicator',
            'sets' => [
                'text' => [
                    'fields' => [
                        ['handle' => 'words', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ];

        $enField = (new Field('content', $fieldConfig))->setParent($enEntry);
        $enFields = $enField->fieldtype()->fields('text');

        $frField = (new Field('content', $fieldConfig))->setParent($frEntry);
        $frFields = $frField->fieldtype()->fields('text');

        // Each locale must get its own Blink cache entry so the Fields instance
        // carries the correct parent. Without the fix, $frFields would be the
        // same cached object as $enFields (containing the en entry as parent).
        $this->assertNotSame($enFields, $frFields);
        $this->assertSame($enEntry, $enFields->all()->first()->parent());
        $this->assertSame($frEntry, $frFields->all()->first()->parent());
    }

    #[Test]
    public function it_has_button_label_config()
    {
        $configFields = collect((new \ReflectionMethod(Replicator::class, 'configFieldItems'))->invoke(new Replicator))
            ->flatMap(fn ($section) => $section['fields']);

        $this->assertSame('text', $configFields['button_label']['type']);
        $this->assertSame('Add Set Label', $configFields['button_label']['display']);
        $this->assertSame('Add Set', $configFields['button_label']['placeholder']);
    }

    #[Test]
    public function it_gets_flattened_sets_config_for_each_field_it_is_given()
    {
        $fieldtype = new Replicator;

        $fieldtype->setField($this->fieldWithSet('alpha'));
        $this->assertSame(['alpha'], $fieldtype->flattenedSetsConfig()->keys()->all());

        $fieldtype->setField($this->fieldWithSet('bravo'));
        $this->assertSame(['bravo'], $fieldtype->flattenedSetsConfig()->keys()->all());
    }

    #[Test]
    public function it_gets_flattened_sets_config_when_the_field_is_replaced_without_being_read()
    {
        // The field is cloned into the fieldtype, so replacing it frees the previous
        // clone and its spl_object_id becomes available to the next one.
        $fieldtype = new Replicator;

        $fieldtype->setField($this->fieldWithSet('alpha'));
        $fieldtype->flattenedSetsConfig();

        $fieldtype->setField($this->fieldWithSet('bravo'));
        $fieldtype->setField($this->fieldWithSet('charlie'));

        $this->assertSame(['charlie'], $fieldtype->flattenedSetsConfig()->keys()->all());
    }

    #[Test]
    public function it_doesnt_use_another_fields_flattened_sets_config_when_cloned()
    {
        $fieldtype = new Replicator;
        $fieldtype->setField($this->fieldWithSet('alpha'));
        $fieldtype->flattenedSetsConfig();

        $clone = (clone $fieldtype)->setField($this->fieldWithSet('bravo'));

        $this->assertSame(['bravo'], $clone->flattenedSetsConfig()->keys()->all());
        $this->assertSame(['alpha'], $fieldtype->flattenedSetsConfig()->keys()->all());
    }

    private function fieldWithSet(string $set)
    {
        return new Field($set.'_field', [
            'type' => 'replicator',
            'sets' => [
                'main' => [
                    'sets' => [
                        $set => ['fields' => [['handle' => 'words', 'field' => ['type' => 'text']]]],
                    ],
                ],
            ],
        ]);
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
