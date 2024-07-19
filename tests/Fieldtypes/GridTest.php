<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldRepository;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Grid;
use Statamic\Fieldtypes\RowId;
use Tests\TestCase;

class GridTest extends TestCase
{
    #[Test]
    public function it_preprocesses_the_values()
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
            'type' => 'grid',
            'fields' => [
                ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
            ],
        ]))->setValue([
            [
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
            ],
        ]);

        $this->assertSame([
            [
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                '_id' => 'random-string-1',
            ],
            [
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
                '_id' => 'random-string-2',
            ],
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses_the_values_recursively()
    {
        $this->partialMock(RowId::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')->times(6)->andReturn(
                'random-string-1',
                'random-string-2',
                'random-string-3',
                'random-string-4',
                'random-string-5',
                'random-string-6',
            );
        });

        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
                ['handle' => 'nested_grid', 'field' => [
                    'type' => 'grid', 'fields' => [
                        ['handle' => 'nested_numbers', 'field' => 'testfieldset.numbers'],
                        ['handle' => 'nested_words', 'field' => ['type' => 'text']],
                    ],
                ]],
            ],
        ]))->setValue([
            [
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        'nested_numbers' => '3', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        'nested_numbers' => '4', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
            [
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        'nested_numbers' => '5', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        'nested_numbers' => '6', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            [
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_grid' => [
                    [
                        'nested_numbers' => 3,
                        'nested_words' => 'nested test one',
                        'nested_foo' => 'nested bar one',
                        '_id' => 'random-string-1',
                    ],
                    [
                        'nested_numbers' => 4,
                        'nested_words' => 'nested test two',
                        'nested_foo' => 'nested bar two',
                        '_id' => 'random-string-2',
                    ],
                ],
                '_id' => 'random-string-3',
            ],
            [
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
                'nested_grid' => [
                    [
                        'nested_numbers' => 5,
                        'nested_words' => 'more nested test one',
                        'nested_foo' => 'more nested bar one',
                        '_id' => 'random-string-4',
                    ],
                    [
                        'nested_numbers' => 6,
                        'nested_words' => 'more nested test two',
                        'nested_foo' => 'more nested bar two',
                        '_id' => 'random-string-5',
                    ],
                ],
                '_id' => 'random-string-6',
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
            'type' => 'grid',
            'fields' => [
                ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
            ],
        ]))->setValue([
            [
                '_id' => 'id-1', // comes from vue
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                '_id' => 'id-2', // comes from vue
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
            ],
        ]);

        $this->assertSame([
            [
                'id' => 'id-1',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
            ],
            [
                'id' => 'id-2',
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
            ],
        ], $field->process()->value());
    }

    #[Test]
    public function it_processes_the_values_recursively_if_ids_inside_sets_are_allowed()
    {
        config()->set('statamic.system.row_id_handle', '_id');

        FieldRepository::shouldReceive('find')
            ->with('testfieldset.numbers')
            ->andReturnUsing(function () {
                return new Field('numbers', ['type' => 'integer']);
            });

        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
                ['handle' => 'nested_grid', 'field' => ['type' => 'grid', 'fields' => [
                    ['handle' => 'nested_numbers', 'field' => 'testfieldset.numbers'],
                    ['handle' => 'nested_words', 'field' => ['type' => 'text']],
                ]]],
            ],
        ]))->setValue([
            [
                '_id' => 'id-1', // comes from vue
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        '_id' => 'id-1-1', // comes from vue
                        'nested_numbers' => '3', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        '_id' => 'id-1-2', // comes from vue
                        'nested_numbers' => '4', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
            [
                '_id' => 'id-2', // comes from vue
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        '_id' => 'id-2-1', // comes from vue
                        'nested_numbers' => '5', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        '_id' => 'id-2-2', // comes from vue
                        'nested_numbers' => '6', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            [
                '_id' => 'id-1',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_grid' => [
                    [
                        '_id' => 'id-1-1',
                        'nested_numbers' => 3,
                        'nested_words' => 'nested test one',
                        'nested_foo' => 'nested bar one',
                    ],
                    [
                        '_id' => 'id-1-2',
                        'nested_numbers' => 4,
                        'nested_words' => 'nested test two',
                        'nested_foo' => 'nested bar two',
                    ],
                ],
            ],
            [
                '_id' => 'id-2',
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
                'nested_grid' => [
                    [
                        '_id' => 'id-2-1',
                        'nested_numbers' => 5,
                        'nested_words' => 'more nested test one',
                        'nested_foo' => 'more nested bar one',
                    ],
                    [
                        '_id' => 'id-2-2',
                        'nested_numbers' => 6,
                        'nested_words' => 'more nested test two',
                        'nested_foo' => 'more nested bar two',
                    ],
                ],
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
            'type' => 'grid',
            'fields' => [
                ['handle' => 'numbers', 'field' => 'testfieldset.numbers'], // test field reference
                ['handle' => 'words', 'field' => ['type' => 'text']], // test inline field
                ['handle' => 'nested_grid', 'field' => ['type' => 'grid', 'fields' => [
                    ['handle' => 'nested_numbers', 'field' => 'testfieldset.numbers'],
                    ['handle' => 'nested_words', 'field' => ['type' => 'text']],
                ]]],
            ],
        ]))->setValue([
            [
                '_id' => 'id-1', // comes from vue
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        '_id' => 'id-1-1', // comes from vue
                        'nested_numbers' => '3', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        '_id' => 'id-1-2', // comes from vue
                        'nested_numbers' => '4', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
            [
                '_id' => 'id-2', // comes from vue
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        '_id' => 'id-2-1', // comes from vue
                        'nested_numbers' => '5', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        '_id' => 'id-2-2', // comes from vue
                        'nested_numbers' => '6', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            [
                'id' => 'id-1',
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_grid' => [
                    [
                        'id' => 'id-1-1',
                        'nested_numbers' => 3,
                        'nested_words' => 'nested test one',
                        'nested_foo' => 'nested bar one',
                    ],
                    [
                        'id' => 'id-1-2',
                        'nested_numbers' => 4,
                        'nested_words' => 'nested test two',
                        'nested_foo' => 'nested bar two',
                    ],
                ],
            ],
            [
                'id' => 'id-2',
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
                'nested_grid' => [
                    [
                        'id' => 'id-2-1',
                        'nested_numbers' => 5,
                        'nested_words' => 'more nested test one',
                        'nested_foo' => 'more nested bar one',
                    ],
                    [
                        'id' => 'id-2-2',
                        'nested_numbers' => 6,
                        'nested_words' => 'more nested test two',
                        'nested_foo' => 'more nested bar two',
                    ],
                ],
            ],
        ], $field->process()->value());
    }

    #[Test]
    public function it_preloads_preprocessed_default_values()
    {
        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                ['handle' => 'things', 'field' => ['type' => 'array']],
            ],
        ]));

        $expected = [
            'things' => [],
        ];

        $this->assertSame($expected, $field->fieldtype()->preload()['defaults']);
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
            'type' => 'grid',
            'fields' => [
                ['handle' => 'words', 'field' => ['type' => 'test']],
            ],
        ]);

        $augmented = $field->fieldtype()->augment([
            ['id' => '1', 'words' => 'one'],
            ['words' => 'two'], // id intentionally omitted
        ]);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals([
            ['id' => '1', 'words' => 'one (augmented)'],
            ['id' => null, 'words' => 'two (augmented)'],
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
            'type' => 'grid',
            'fields' => [
                ['handle' => 'words', 'field' => ['type' => 'test']],
                ['handle' => 'id', 'field' => ['type' => 'test']],
            ],
        ]);

        $augmented = $field->fieldtype()->augment([
            ['_id' => '1', 'id' => '7', 'words' => 'one'],
            ['id' => '8', 'words' => 'two'], // row id intentionally omitted
            ['_id' => '3', 'words' => 'three'], // id field intentionally omitted
        ]);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals([
            ['_id' => '1', 'id' => '7 (augmented)', 'words' => 'one (augmented)'],
            ['_id' => null, 'id' => '8 (augmented)', 'words' => 'two (augmented)'],
            ['_id' => '3', 'id' => ' (augmented)', 'words' => 'three (augmented)'],
        ], collect($augmented)->toArray());
    }

    #[Test]
    public function it_converts_a_queryable_value()
    {
        $this->assertNull((new Grid)->toQueryableValue(null));
        $this->assertNull((new Grid)->toQueryableValue([]));
        $this->assertEquals([['foo' => 'bar']], (new Grid)->toQueryableValue([['foo' => 'bar']]));
    }

    #[Test]
    public function it_generates_field_path_prefix()
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
            'type' => 'grid',
            'fields' => [
                ['handle' => 'words', 'field' => ['type' => 'custom']],
            ],
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
        $this->assertEquals('test.-1.words', $value['new']['words']['fieldPathPrefix']);
        $this->assertEquals('test.-1.words', $value['defaults']['words']);
    }
}
