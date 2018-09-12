<?php

namespace Tests\Fields;

use Statamic\Fields\Fieldset;
use Tests\TestCase;
use Statamic\Addons\Grid\GridFieldtype as Grid;
use Statamic\Addons\Fields\FieldsFieldtype as Fields;
use App\Fieldtypes\Fieldtype;
use Statamic\Addons\Replicator\ReplicatorFieldtype as Replicator;
use Statamic\Addons\ReplicatorSets\ReplicatorSetsFieldtype as ReplicatorSets;
use Facades\Tests\FakeFieldsetLoader;
use Facades\Tests\FakeFieldtypeLoader;
use Facades\Tests\Factories\FieldsetFactory;
use Tests\Fixtures\Fieldtypes\PlainFieldtype;
use Tests\Fixtures\Fieldtypes\FieldtypeThatPreprocesses;
use Tests\Fixtures\Fieldtypes\FieldtypeWithPreprocessableConfigField;

class FieldsetTest extends TestCase
{
    /** @test */
    function it_gets_and_sets_the_contents()
    {
        $fieldset = FieldsetFactory::create();
        $this->assertNull($fieldset->contents());
        $this->assertEquals($fieldset, $fieldset->contents(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $fieldset->contents());
    }

    /** @test */
    function it_gets_the_sections()
    {
        $sections = [
            'main' => [
                'fields' => ['hello' => 'world']
            ]
        ];

        $fieldset = FieldsetFactory::withSections($sections)->create();

        $this->assertEquals($sections, $fieldset->sections());
    }

    /** @test */
    function it_places_top_level_fields_in_main_section()
    {
        $fields = ['hello' => 'world'];

        $fieldset = FieldsetFactory::withContents([
            'title' => 'Test',
            'fields' => $fields,
        ])->create();

        $this->assertEquals(['main' => ['fields' => $fields]], $fieldset->sections());
    }

    /** @test */
    function fields_are_at_the_end_of_sections_for_readability()
    {
        $fieldset = FieldsetFactory::withSections([
            'main' => [
                'fields' => ['hello' => 'world'],
                'display' => 'Main',
            ]
        ])->create();

        $this->assertEquals(
            ['display', 'fields'],
            array_keys($fieldset->sections()['main'])
        );
    }

    /** @test */
    function it_flattens_fields_from_all_sections()
    {
        $fieldset = FieldsetFactory::withSections([
            'section_one' => [
                'fields' => [
                    'one' => ['type' => 'text'],
                    'two' => ['type' => 'text'],
                ]
            ],
            'section_two' => [
                'fields' => [
                    'three' => ['type' => 'text'],
                    'four' => ['type' => 'text'],
                ]
            ]
        ])->create();

        $this->assertEquals([
            'one' => ['type' => 'text'],
            'two' => ['type' => 'text'],
            'three' => ['type' => 'text'],
            'four' => ['type' => 'text'],
        ], $fieldset->fields());
    }

    /** @test */
    function it_brings_fields_inline_from_partial_fieldsets_recursively()
    {
        FakeFieldsetLoader::bind()
            ->with('example', function ($fieldset) {
                return $fieldset->withFields([
                    'partial_field_one' => ['type' => 'text'],
                    'partial_field_two' => ['type' => 'partial', 'fieldset' => 'nested'],
                ]);
            })
            ->with('nested', function ($fieldset) {
                return $fieldset->withFields([
                    'nested_partial_field_one' => ['type' => 'text'],
                    'nested_partial_field_two' => ['type' => 'text'],
                ]);
            });

        $fieldset = FieldsetFactory::withSections([
            'section_one' => [
                'fields' => [
                    'field_one' => ['type' => 'text'],
                    'field_two' => ['type' => 'partial', 'fieldset' => 'example'],
                ]
            ],
            'section_two' => [
                'fields' => [
                    'field_three' => ['type' => 'text'],
                    'field_four' => ['type' => 'text'],
                ]
            ]
        ])->create();

        $this->assertEquals([
            'field_one' => ['type' => 'text'],
            'field_two' => ['type' => 'partial', 'fieldset' => 'example'],
            'field_three' => ['type' => 'text'],
            'field_four' => ['type' => 'text'],
        ], $fieldset->fields());

        $this->assertEquals([
            'field_one' => ['type' => 'text'],
            'partial_field_one' => ['type' => 'text'],
            'nested_partial_field_one' => ['type' => 'text'],
            'nested_partial_field_two' => ['type' => 'text'],
            'field_three' => ['type' => 'text'],
            'field_four' => ['type' => 'text'],
        ], $fieldset->inlinedFields());
    }

    /** @test */
    function grid_fields_can_have_fields_inlined()
    {
        FakeFieldsetLoader::bind()->with('example', function ($fieldset) {
            return $fieldset->withFields([
                'partial_field_one' => ['type' => 'text'],
                'partial_field_two' => ['type' => 'text'],
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'field_one' => ['type' => 'text'],
            'field_two' => [
                'type' => 'grid',
                'fields' => [
                    'grid_field_one' => ['type' => 'text'],
                    'grid_field_two' => ['type' => 'partial', 'fieldset' => 'example'],
                ]
            ],
            'field_three' => ['type' => 'text'],
        ])->create();

        $this->assertEquals([
            'field_one' => ['type' => 'text'],
            'field_two' => [
                'type' => 'grid',
                'fields' => [
                    'grid_field_one' => ['type' => 'text'],
                    'grid_field_two' => ['type' => 'partial', 'fieldset' => 'example'],
                ]
            ],
            'field_three' => ['type' => 'text'],
        ], $fieldset->fields());

        $this->assertEquals([
            'field_one' => ['type' => 'text'],
            'field_two' => [
                'type' => 'grid',
                'fields' => [
                    'grid_field_one' => ['type' => 'text'],
                    'partial_field_one' => ['type' => 'text'],
                    'partial_field_two' => ['type' => 'text'],
                ]
            ],
            'field_three' => ['type' => 'text'],
        ], $fieldset->inlinedFields());
    }

    /** @test */
    function replicator_fields_can_have_fields_inlined()
    {
        FakeFieldsetLoader::bind()->with('example', function ($fieldset) {
            return $fieldset->withFields([
                'partial_field_one' => ['type' => 'text'],
                'partial_field_two' => ['type' => 'text'],
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'field_one' => ['type' => 'text'],
            'field_two' => [
                'type' => 'replicator',
                'sets' => [
                    'set_one' => [
                        'fields' => [
                            'set_field_one' => ['type' => 'text'],
                            'set_field_two' => ['type' => 'partial', 'fieldset' => 'example'],
                        ]
                    ],
                ]
            ],
            'field_three' => ['type' => 'text'],
        ])->create();

        $this->assertEquals([
            'field_one' => ['type' => 'text'],
            'field_two' => [
                'type' => 'replicator',
                'sets' => [
                    'set_one' => [
                        'fields' => [
                            'set_field_one' => ['type' => 'text'],
                            'set_field_two' => ['type' => 'partial', 'fieldset' => 'example'],
                        ]
                    ]
                ]
            ],
            'field_three' => ['type' => 'text'],
        ], $fieldset->fields());

        $this->assertEquals([
            'field_one' => ['type' => 'text'],
            'field_two' => [
                'type' => 'replicator',
                'sets' => [
                    'set_one' => [
                        'fields' => [
                            'set_field_one' => ['type' => 'text'],
                            'partial_field_one' => ['type' => 'text'],
                            'partial_field_two' => ['type' => 'text'],
                        ]
                    ]
                ]
            ],
            'field_three' => ['type' => 'text'],
        ], $fieldset->inlinedFields());
    }

    /** @test */
    function it_gets_the_fieldtypes()
    {
        FakeFieldtypeLoader::bind()
            ->with('foo', PlainFieldtype::class)
            ->with('bar', PlainFieldtype::class);

        $fieldset = FieldsetFactory::withFields([
            'one' => ['type' => 'foo', 'example' => 'test'],
            'two' => ['type' => 'bar', 'test' => 'example'],
        ])->create();

        $fieldtypes = $fieldset->fieldtypes();

        $this->assertEquals(2, count($fieldtypes));
        $this->assertNotSame($fieldtypes[0], $fieldtypes[1]);

        tap($fieldtypes[0], function ($fieldtype) {
            $this->assertInstanceOf(PlainFieldtype::class, $fieldtype);
            $this->assertEquals(['type' => 'foo', 'example' => 'test', 'name' => 'one'], $fieldtype->getFieldConfig());
        });

        tap($fieldtypes[1], function ($fieldtype) {
            $this->assertInstanceOf(PlainFieldtype::class, $fieldtype);
            $this->assertEquals(['type' => 'bar', 'test' => 'example', 'name' => 'two'], $fieldtype->getFieldConfig());
        });
    }

    /** @test */
    function each_config_value_in_each_field_should_by_preprocessed_by_its_fieldtype()
    {
        FakeFieldtypeLoader::bind()
            ->with('foo', FieldtypeWithPreprocessableConfigField::class)
            ->with('baz', FieldtypeThatPreprocesses::class);

        $fieldset = FieldsetFactory::withFields([
            'one' => ['type' => 'foo', 'test' => 'foo'],
        ])->create();

        $this->assertArraySubset([
            'one' => ['type' => 'foo', 'test' => 'preprocessed foo']
        ], $fieldset->preProcessFields($fieldset->fields()));
    }

    /** @test */
    function each_config_value_in_each_field_should_by_preprocessed_by_its_fieldtype_with_partials()
    {
        FakeFieldtypeLoader::bind()
            ->with('foo', FieldtypeWithPreprocessableConfigField::class)
            ->with('baz', FieldtypeThatPreprocesses::class);

        FakeFieldsetLoader::bind()->with('partial', function ($fieldset) {
            return $fieldset->withFields([
                'partial_field_one' => ['type' => 'foo', 'test' => 'foo'],
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'one' => ['type' => 'foo', 'test' => 'foo'],
            'two' => ['type' => 'partial', 'fieldset' => 'partial']
        ])->create();

        $this->assertArraySubset([
            'one' => ['type' => 'foo', 'test' => 'preprocessed foo'],
            'partial_field_one' => ['type' => 'foo', 'test' => 'preprocessed foo']
        ], $fieldset->preProcessFields($fieldset->inlinedFields()));
    }

    /** @test */
    function each_config_value_in_each_field_should_by_preprocessed_by_its_fieldtype_with_partials_in_grid()
    {
        FakeFieldtypeLoader::bind()
            ->with('foo', FieldtypeWithPreprocessableConfigField::class)
            ->with('baz', FieldtypeThatPreprocesses::class)
            ->with('grid', Grid::class)
            ->with('fields', Fields::class);

        FakeFieldsetLoader::bind()->with('partial', function ($fieldset) {
            return $fieldset->withFields([
                'partial_field_one' => ['type' => 'foo', 'test' => 'foo'],
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'one' => ['type' => 'foo', 'test' => 'foo'],
            'two' => ['type' => 'grid', 'fields' => [
                'one' => ['type' => 'foo', 'test' => 'foo'],
                'partial' => ['type' => 'partial', 'fieldset' => 'partial'],
            ]],
        ])->create();

        $this->assertArraySubset([
            'one' => ['type' => 'foo', 'test' => 'preprocessed foo'],
            'two' => ['type' => 'grid', 'fields' => [
                ['type' => 'foo', 'test' => 'preprocessed foo', 'handle' => 'one'],
                ['type' => 'foo', 'test' => 'preprocessed foo', 'handle' => 'partial_field_one']
            ]]
        ], $fieldset->preProcessFields($fieldset->inlinedFields()));
    }


    /** @test */
    function each_config_value_in_each_field_should_by_preprocessed_by_its_fieldtype_with_partials_in_replicator()
    {
        FakeFieldtypeLoader::bind()
            ->with('foo', FieldtypeWithPreprocessableConfigField::class)
            ->with('baz', FieldtypeThatPreprocesses::class)
            ->with('replicator', Replicator::class)
            ->with('replicator_sets', ReplicatorSets::class);

        FakeFieldsetLoader::bind()->with('partial', function ($fieldset) {
            return $fieldset->withFields([
                'partial_field_one' => ['type' => 'foo', 'test' => 'foo'],
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'one' => ['type' => 'foo', 'test' => 'foo'],
            'two' => ['type' => 'replicator', 'sets' => [
                [
                    'handle' => 'set_one',
                    'fields' => [
                        ['handle' => 'one', 'type' => 'foo', 'test' => 'foo'],
                        ['handle' => 'partial', 'type' => 'partial', 'fieldset' => 'partial'],
                    ]
                ]
            ]]
        ])->create();

        $this->assertArraySubset([
            'one' => ['type' => 'foo', 'test' => 'preprocessed foo'],
            'two' => ['type' => 'replicator', 'sets' => [
                [
                    'handle' => 'set_one',
                    'fields' => [
                        ['handle' => 'one', 'type' => 'foo', 'test' => 'preprocessed foo'],
                        ['handle' => 'partial_field_one', 'type' => 'foo', 'test' => 'preprocessed foo']
                    ]
                ]
            ]]
        ], $fieldset->preProcessFields($fieldset->inlinedFields()));
    }

    /** @test */
    function converts_to_array_to_be_used_in_publish_forms()
    {
        FakeFieldsetLoader::bind()->with('partial', function ($fieldset) {
            return $fieldset->withFields([
                'two' => [
                    'type' => 'foo',
                    'test' => 'foo'
                ]
            ]);
        });

        FakeFieldtypeLoader::bind()
            ->with('text', PlainFieldtype::class)
            ->with('foo', FieldtypeWithPreprocessableConfigField::class)
            ->with('baz', FieldtypeThatPreprocesses::class);

        $fieldset = (new Fieldset)->name('test')->contents([
            'taxonomies' => true,
            'fields' => [
                'one' => [
                    'type' => 'foo',
                    'test' => 'foo',
                    'display' => 'Foo',
                    'instructions' => 'The foo instructions',
                ],
                'partial' => [
                    'type' => 'partial',
                    'fieldset' => 'partial'
                ],
                'three' => '',
            ],
        ]);

        $expected = [
            'name' => 'test',
            'taxonomies' => true,
            'sections' => [
                [
                    'handle' => 'main',
                    'fields' => [
                        [
                            'handle' => 'one',
                            'type' => 'foo',
                            'test' => 'preprocessed foo',
                            'display' => 'Foo',
                            'instructions' => 'The foo instructions',
                            'required' => false
                        ],
                        [
                            'handle' => 'two',
                            'type' => 'foo',
                            'test' => 'preprocessed foo',
                            'display' => null,
                            'instructions' => null,
                            'required' => false
                        ],
                        [
                            'handle' => 'three',
                            'type' => 'text',
                            'display' => null,
                            'instructions' => null,
                            'required' => false
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $fieldset->toPublishArray());
    }
}
