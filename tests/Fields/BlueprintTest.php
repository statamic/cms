<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldsetRepository;
use Illuminate\Support\Collection;
use Statamic\Facades\Field as FieldAPI;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Section;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    /** @test */
    function it_gets_the_handle()
    {
        $blueprint = new Blueprint;
        $this->assertNull($blueprint->handle());

        $return = $blueprint->setHandle('test');

        $this->assertEquals($blueprint, $return);
        $this->assertEquals('test', $blueprint->handle());
    }

    /** @test */
    function it_gets_contents()
    {
        $blueprint = new Blueprint;
        $this->assertEquals([], $blueprint->contents());

        $contents = [
            'sections' => [
                'main' => [
                    'fields' => ['one' => ['type' => 'text']]
                ]
            ]
        ];

        $return = $blueprint->setContents($contents);

        $this->assertEquals($blueprint, $return);
        $this->assertEquals($contents, $blueprint->contents());
    }

    /** @test */
    function it_gets_the_title()
    {
        $blueprint = (new Blueprint)->setContents([
            'title' => 'Test'
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    /** @test */
    function the_title_falls_back_to_a_humanized_handle()
    {
        $blueprint = (new Blueprint)->setHandle('the_blueprint_handle');

        $this->assertEquals('The blueprint handle', $blueprint->title());
    }

    /** @test */
    function it_gets_sections()
    {
        $blueprint = new Blueprint;
        tap($blueprint->sections(), function ($sections) {
            $this->assertInstanceOf(Collection::class, $sections);
            $this->assertCount(0, $sections);
        });

        $contents = [
            'sections' => [
                'section_one' => [
                    'fields' => ['one' => ['type' => 'text']]
                ],
                'section_two' => [
                    'fields' => ['two' => ['type' => 'text']]
                ]
            ]
        ];

        $blueprint->setContents($contents);

        tap($blueprint->sections(), function ($sections) {
            $this->assertCount(2, $sections);
            $this->assertEveryItemIsInstanceOf(Section::class, $sections);
            $this->assertEquals(['section_one', 'section_two'], $sections->map->handle()->values()->all());
        });
    }

    /** @test */
    function it_puts_top_level_fields_into_a_main_section()
    {
        $blueprint = new Blueprint;
        $this->assertEquals([], $blueprint->contents());

        $blueprint->setContents([
            'fields' => ['one' => ['type' => 'text']]
        ]);

        $this->assertEquals([
            'sections' => [
                'main' => [
                    'fields' => ['one' => ['type' => 'text']]
                ]
            ]
        ], $blueprint->contents());
    }

    /** @test */
    function it_can_check_if_has_field()
    {
        FieldsetRepository::shouldReceive('find')
            ->andReturn((new Fieldset)->setHandle('partial')->setContents([
                'title' => 'Partial',
                'fields' => [
                    'three' => ['type' => 'text'],
                ],
            ]));

        $blueprint = (new Blueprint)->setHandle('sectioned')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                        ['import' => 'partial'],
                    ]
                ]
            ]
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
    function it_gets_fields()
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
                            'field' => 'fieldset_one.field_one'
                        ]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two'
                        ]
                    ]
                ]
            ]
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
    function converts_to_array_suitable_for_rendering_fields_in_publish_component()
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
                'validate' => 'min:2'
            ]));

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'instructions' => 'Does stuff',
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one'
                        ]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two'
                        ]
                    ]
                ]
            ]
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
                        ]
                    ]
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
                        ]
                    ]
                ]
            ],
            'empty' => false,
        ], $blueprint->toPublishArray());
    }

    /** @test */
    function it_saves_through_the_repository()
    {
        BlueprintRepository::shouldReceive('save')->with($blueprint = new Blueprint)->once();

        $return = $blueprint->save();

        $this->assertEquals($blueprint, $return);
    }

    /** @test */
    function it_ensures_a_field_exists_if_it_doesnt()
    {
        FieldsetRepository::shouldReceive('find')
            ->andReturn((new Fieldset)->setHandle('partial')->setContents([
                'title' => 'Partial',
                'fields' => [
                    'three' => ['type' => 'text'],
                ],
            ]));

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                        ['import' => 'partial'],
                    ]
                ]
            ]
        ]);
        $this->assertFalse($blueprint->hasField('four'));

        $return = $blueprint
            ->ensureField('four', ['type' => 'textarea']) // field "four" doesnt exist.
            ->ensureField('two', ['type' => 'textarea', 'foo' => 'bar'])  // field "two" exists in blueprint.
            ->ensureField('three', ['type' => 'textarea', 'foo' => 'baz']); // field "three" exists in partial.

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('four'));
        tap($blueprint->fields()->all(), function ($items) {
            $this->assertCount(4, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $this->assertEquals([
                'one' => ['type' => 'text'],
                'two' => ['type' => 'text', 'foo' => 'bar'], // config gets merged, but keys in the blueprint win.
                'three' => ['type' => 'text', 'foo' => 'baz'], // config gets merged, but keys in partial win.
                'four' => ['type' => 'textarea'], // field gets added.
            ], $items->map->config()->all());
        });
    }

    /** @test */
    function it_ensures_a_field_exists_if_it_doesnt_and_prepends_it()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']]
                    ]
                ]
            ]
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
    function it_ensures_a_field_exists_in_a_given_section_if_it_doesnt_exist_at_all()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text', 'foo' => 'bar']]
                    ]
                ]
            ]
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
    function it_removes_a_field()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                        ['handle' => 'three', 'field' => ['type' => 'text']]
                    ]
                ]
            ]
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
    function it_removes_a_field_from_a_specific_section()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'three', 'field' => ['type' => 'text']],
                        ['handle' => 'four', 'field' => ['type' => 'text']],
                    ]
                ]
            ]
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
    function it_validates_unique_handles()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                        ['import' => 'test'],
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']]
                    ]
                ]
            ]
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate field [one] on blueprint [test].');

        $blueprint->fields();
    }
}
