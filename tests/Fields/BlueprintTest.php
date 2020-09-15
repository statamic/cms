<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldsetRepository;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Section;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    /** @test */
    public function it_gets_the_handle()
    {
        $blueprint = new Blueprint;
        $this->assertNull($blueprint->handle());

        $return = $blueprint->setHandle('test');

        $this->assertEquals($blueprint, $return);
        $this->assertEquals('test', $blueprint->handle());
    }

    /** @test */
    public function it_gets_contents()
    {
        $blueprint = new Blueprint;
        $this->assertEquals(['sections' => ['main' => ['fields' => []]]], $blueprint->contents());

        $contents = [
            'sections' => [
                'main' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ];

        $return = $blueprint->setContents($contents);

        $this->assertEquals($blueprint, $return);
        $this->assertEquals($contents, $blueprint->contents());
    }

    /** @test */
    public function it_gets_the_title()
    {
        $blueprint = (new Blueprint)->setContents([
            'title' => 'Test',
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    /** @test */
    public function the_title_falls_back_to_a_humanized_handle()
    {
        $blueprint = (new Blueprint)->setHandle('the_blueprint_handle');

        $this->assertEquals('The blueprint handle', $blueprint->title());
    }

    /** @test */
    public function it_gets_sections()
    {
        $blueprint = new Blueprint;
        tap($blueprint->sections(), function ($sections) {
            $this->assertInstanceOf(Collection::class, $sections);
            $this->assertCount(1, $sections);
        });

        $contents = [
            'sections' => [
                'section_one' => [
                    'fields' => ['one' => ['type' => 'text']],
                ],
                'section_two' => [
                    'fields' => ['two' => ['type' => 'text']],
                ],
            ],
        ];

        $blueprint->setContents($contents);

        tap($blueprint->sections(), function ($sections) {
            $this->assertCount(2, $sections);
            $this->assertEveryItemIsInstanceOf(Section::class, $sections);
            $this->assertEquals(['section_one', 'section_two'], $sections->map->handle()->values()->all());
        });
    }

    /** @test */
    public function it_puts_top_level_fields_into_a_main_section()
    {
        $blueprint = new Blueprint;
        $this->assertEquals(['sections' => ['main' => ['fields' => []]]], $blueprint->contents());

        $blueprint->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
            ],
        ]);

        $this->assertEquals([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
        ], $blueprint->contents());
    }

    /** @test */
    public function it_can_check_if_has_field()
    {
        FieldsetRepository::shouldReceive('find')
            ->andReturn((new Fieldset)->setHandle('partial')->setContents([
                'title' => 'Partial',
                'fields' => [
                    [
                        'handle' => 'three',
                        'field' => ['type' => 'text'],
                    ],
                ],
            ]));

        $blueprint = (new Blueprint)->setHandle('sectioned')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                        ['import' => 'partial'],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertTrue($blueprint->hasField('three'));
        $this->assertFalse($blueprint->hasField('four')); // Doesnt exist

        $this->assertTrue($blueprint->hasFieldInSection('one', 'section_one'));
        $this->assertTrue($blueprint->hasFieldInSection('two', 'section_two'));
        $this->assertTrue($blueprint->hasFieldInSection('three', 'section_two'));
        $this->assertFalse($blueprint->hasFieldInSection('one', 'section_two')); // In section one
        $this->assertFalse($blueprint->hasFieldInSection('three', 'section_one')); // In section two
        $this->assertFalse($blueprint->hasFieldInSection('four', 'section_two')); // Doesnt exist
    }

    /** @test */
    public function it_gets_fields()
    {
        $blueprint = new Blueprint;
        tap($blueprint->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $blueprint->setContents($contents = [
            'sections' => [
                'section_one' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one',
                        ],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($blueprint->hasField('one'));
        $this->assertInstanceOf(Field::class, $blueprint->field('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertInstanceOf(Field::class, $blueprint->field('two'));
        $this->assertFalse($blueprint->hasField('three'));
        $this->assertNull($blueprint->field('three'));

        tap($blueprint->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            tap($fields->all(), function ($items) {
                $this->assertCount(2, $items);
                $this->assertEveryItemIsInstanceOf(Field::class, $items);
                $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
                $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
            });
        });
    }

    /** @test */
    public function it_gets_columns()
    {
        $blueprint = new Blueprint;

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $blueprint->setContents($contents = [
            'sections' => [
                'section_one' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one',
                        ],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two',
                        ],
                    ],
                ],
            ],
        ]);

        tap($blueprint->columns(), function ($columns) {
            $this->assertInstanceOf(Columns::class, $columns);
            tap($columns, function ($items) {
                $this->assertCount(2, $items);
                $this->assertEveryItemIsInstanceOf(Column::class, $items);
                $this->assertEquals(['one', 'two'], $items->map->field()->values()->all());
                $this->assertEquals([1, 2], $items->map->defaultOrder()->values()->all());
            });
        });
    }

    /** @test */
    public function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', [
                'type' => 'text',
                'display' => 'One',
                'instructions' => 'One instructions',
                'validate' => 'required|min:2',
            ]));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_two', [
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'validate' => 'min:2',
            ]));

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'instructions' => 'Does stuff',
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one',
                        ],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            'title' => 'Test',
            'handle' => 'test',
            'sections' => [
                [
                    'display' => 'Section one',
                    'handle' => 'section_one',
                    'instructions' => 'Does stuff',
                    'fields' => [
                        [
                            'handle' => 'one',
                            'type' => 'text',
                            'display' => 'One',
                            'instructions' => 'One instructions',
                            'required' => true,
                            'validate' => 'required|min:2',
                            'component' => 'text',
                            'placeholder' => null,
                            'character_limit' => 0,
                            'input_type' => 'text',
                            'prepend' => null,
                            'append' => null,
                        ],
                    ],
                ],
                [
                    'display' => 'Section two',
                    'handle' => 'section_two',
                    'instructions' => null,
                    'fields' => [
                        [
                            'handle' => 'two',
                            'type' => 'textarea',
                            'display' => 'Two',
                            'instructions' => 'Two instructions',
                            'required' => false,
                            'validate' => 'min:2',
                            'character_limit' => null,
                            'component' => 'textarea',
                        ],
                    ],
                ],
            ],
            'empty' => false,
        ], $blueprint->toPublishArray());
    }

    /** @test */
    public function it_saves_through_the_repository()
    {
        BlueprintRepository::shouldReceive('save')->with($blueprint = new Blueprint)->once();

        $return = $blueprint->save();

        $this->assertEquals($blueprint, $return);
    }

    /** @test */
    public function it_ensures_a_field_exists()
    {
        $blueprint = (new Blueprint)->setContents(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text']],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('new', ['type' => 'textarea']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertTrue($blueprint->hasField('new'));
        $this->assertEquals(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text']],
                    ['handle' => 'new', 'field' => ['type' => 'textarea']],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'textarea'], $blueprint->fields()->get('new')->config());
    }

    /** @test */
    public function it_ensures_a_field_exists_in_a_specific_section()
    {
        $blueprint = (new Blueprint)->setContents(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text']],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('new', ['type' => 'textarea'], 'section_two');

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertTrue($blueprint->hasField('new'));
        $this->assertEquals(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text']],
                ],
            ],
            'section_two' => [
                'fields' => [
                    ['handle' => 'new', 'field' => ['type' => 'textarea']],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'textarea'], $blueprint->fields()->get('new')->config());
    }

    /** @test */
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_a_field_exists_and_it_already_exists()
    {
        $blueprint = (new Blueprint)->setContents(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text']],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('existing', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertEquals(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('existing')->config());
    }

    /** @test */
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_a_field_exists_and_it_already_exists_in_a_specific_section()
    {
        $blueprint = (new Blueprint)->setContents(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text']],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('existing', ['type' => 'textarea', 'foo' => 'bar'], 'another_section');

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertEquals(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('existing')->config());
    }

    /** @test */
    public function it_merges_config_overrides_for_previously_undefined_keys_when_ensuring_a_field_and_it_already_exists_as_a_reference()
    {
        FieldsetRepository::shouldReceive('find')->with('the_partial')->andReturn(
            (new Fieldset)->setContents(['fields' => [
                [
                    'handle' => 'the_field',
                    'field' => ['type' => 'text'],
                ],
            ]])
        );

        $blueprint = (new Blueprint)->setContents(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'from_partial', 'field' => 'the_partial.the_field'],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('from_partial', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('from_partial'));
        $this->assertEquals(['sections' => [
            'section_one' => [
                'fields' => [
                    ['handle' => 'from_partial', 'field' => 'the_partial.the_field', 'config' => ['foo' => 'bar']],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('from_partial')->config());
    }

    /** @test */
    public function it_merges_undefined_config_overrides_when_ensuring_a_field_that_already_exists_inside_an_imported_fieldset()
    {
        FieldsetRepository::shouldReceive('find')->with('the_partial')->andReturn(
            (new Fieldset)->setContents(['fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
            ]])
        );

        $blueprint = (new Blueprint)->setContents(['sections' => [
            'section_one' => [
                'fields' => [
                    ['import' => 'the_partial'],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('one', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('one'));
        $this->assertEquals(['sections' => [
            'section_one' => [
                'fields' => [
                    [
                        'import' => 'the_partial',
                        'config' => [
                            'one' => ['foo' => 'bar'],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('one')->config());
    }

    /** @test */
    public function it_ensures_a_field_exists_if_it_doesnt_and_prepends_it()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ]);
        $this->assertFalse($blueprint->hasField('three'));

        $return = $blueprint
            ->ensureFieldPrepended('three', ['type' => 'textarea']); // field "three" doesnt exist, so it should get added to the start.

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('three'));
        tap($blueprint->fields()->all(), function ($items) {
            $this->assertCount(3, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $this->assertEquals(['three', 'one', 'two'], $items->map->handle()->values()->all());
            $this->assertEquals(['textarea', 'text', 'text'], $items->map->type()->values()->all());
        });
    }

    /** @test */
    public function it_ensures_a_field_exists_in_a_given_section_if_it_doesnt_exist_at_all()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text', 'foo' => 'bar']],
                    ],
                ],
            ],
        ]);
        $this->assertFalse($blueprint->hasField('three'));
        $this->assertEquals(2, $blueprint->sections()->count());

        $return = $blueprint
            // field "three" doesnt exist, so it will be added to a new "section_three" section
            ->ensureField('three', ['type' => 'textarea'], 'section_three')
            // field "two" exists, even though its in a different section than the one requested, so the config is merged
            ->ensureField('two', ['type' => 'textarea'], 'section_three');

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('three'));
        $this->assertEquals(3, $blueprint->sections()->count());
        tap($blueprint->fields()->all(), function ($items) {
            $this->assertCount(3, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            // $this->assertEquals(['one', 'two', 'three'], $items->map->handle()->values()->all());
            // $this->assertEquals(['text', 'textarea', 'textarea'], $items->map->type()->values()->all());
            $this->assertEquals([
                'one' => ['type' => 'text'],
                'three' => ['type' => 'textarea'],
                'two' => ['type' => 'text', 'foo' => 'bar'], // config gets merged, but keys in the blueprint win.
            ], $items->map->config()->all());
        });
    }

    /** @test */
    public function it_removes_a_field()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                        ['handle' => 'three', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertTrue($blueprint->hasField('three'));

        $return = $blueprint
            ->removeField('one')
            ->removeField('three')
            ->removeField('four'); // Ensure it doesn't error when field handle not found

        $this->assertEquals($blueprint, $return);

        $this->assertFalse($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two')); // Was never removed
        $this->assertFalse($blueprint->hasField('three'));
    }

    /** @test */
    public function it_removes_a_field_from_a_specific_section()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'three', 'field' => ['type' => 'text']],
                        ['handle' => 'four', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertTrue($blueprint->hasField('three'));
        $this->assertTrue($blueprint->hasField('four'));

        $return = $blueprint
            ->removeField('one', 'section_one')
            ->removeField('four', 'section_one') // Doesn't exist in section one, so it won't be removed.
            ->removeFieldFromSection('three', 'section_two')
            ->removeFieldFromSection('two', 'section_two') // Don't exist in section two, so it won't be removed.
            ->removeField('seven', 'section_one') // Ensure it doesn't error when field doesn't exist at all.
            ->removeFieldFromSection('eight', 'section_one'); // Ensure it doesn't error when field doesn't exist at all.

        $this->assertEquals($blueprint, $return);

        $this->assertFalse($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertFalse($blueprint->hasField('three'));
        $this->assertTrue($blueprint->hasField('four'));
    }

    /** @test */
    public function it_validates_unique_handles()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate field [one] on blueprint [test].');

        $blueprint->fields();
    }

    /** @test */
    public function it_validates_unique_handles_between_blueprint_and_imported_fieldset()
    {
        $fieldset = (new Fieldset)->setContents([
            'fields' => [
                ['handle' => 'one', 'field' => ['type' => 'text']],
            ],
        ]);

        Facades\Fieldset::shouldReceive('find')
            ->with('test')
            ->andReturn($fieldset);

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['import' => 'test'],
                    ],
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate field [one] on blueprint [test].');

        $blueprint->fields();
    }

    /** @test */
    public function it_can_import_the_same_fieldset_twice_with_different_prefixes()
    {
        $fieldset = (new Fieldset)->setContents([
            'fields' => [
                ['handle' => 'one', 'field' => ['type' => 'text']],
            ],
        ]);

        Facades\Fieldset::shouldReceive('find')
            ->with('test')
            ->andReturn($fieldset);

        $blueprint = (new Blueprint)->setHandle('test')
            ->setContents($contents = [
                'title' => 'Test',
                'sections' => [
                    'section_one' => [
                        'fields' => [
                            ['import' => 'test', 'prefix' => 'first_'],
                            ['import' => 'test', 'prefix' => 'second_'],
                        ],
                    ],
                ],
            ])
            ->ensureField('test', ['type' => 'text']); // This was screwing with multiple imports of the same fieldset.

        $this->assertTrue($blueprint->hasField('first_one'));
        $this->assertTrue($blueprint->hasField('second_one'));
    }

    /** @test */
    public function it_gets_the_handle_when_casting_to_a_string()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertEquals('test', (string) $blueprint);
    }

    /** @test */
    public function it_augments()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertInstanceof(Augmentable::class, $blueprint);
        $this->assertEquals([
            'title' => 'Test',
            'handle' => 'test',
        ], $blueprint->toAugmentedArray());
    }

    /** @test */
    public function it_augments_in_the_parser()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertEquals('test', Facades\Antlers::parse('{{ blueprint }}', ['blueprint' => $blueprint]));

        $this->assertEquals('test Test', Facades\Antlers::parse('{{ blueprint }}{{ handle }} {{ title }}{{ /blueprint }}', ['blueprint' => $blueprint]));
    }
}
