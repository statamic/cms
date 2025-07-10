<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\QueryableValue;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Entries\Entry;
use Statamic\Events\BlueprintCreated;
use Statamic\Events\BlueprintCreating;
use Statamic\Events\BlueprintDeleted;
use Statamic\Events\BlueprintDeleting;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\BlueprintSaving;
use Statamic\Facades;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Tab;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    #[Test]
    public function it_gets_the_handle()
    {
        $blueprint = new Blueprint;
        $this->assertNull($blueprint->handle());

        $return = $blueprint->setHandle('test');

        $this->assertEquals($blueprint, $return);
        $this->assertEquals('test', $blueprint->handle());
    }

    #[Test]
    public function it_gets_contents()
    {
        $blueprint = new Blueprint;
        $this->assertEquals(['tabs' => ['main' => ['fields' => []]]], $blueprint->contents());

        $contents = [
            'tabs' => [
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

    #[Test]
    public function it_gets_the_title()
    {
        $blueprint = (new Blueprint)->setContents([
            'title' => 'Test',
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    #[Test]
    public function it_gets_the_hidden_property_which_is_false_by_default()
    {
        $blueprint = new Blueprint;
        $this->assertSame(false, $blueprint->hidden());

        $blueprint->setHidden(true);
        $this->assertSame(true, $blueprint->hidden());

        $blueprint->setHidden(false);
        $this->assertSame(false, $blueprint->hidden());

        $blueprint->setHidden(null);
        $this->assertSame(false, $blueprint->hidden());
    }

    #[Test]
    public function the_title_falls_back_to_a_humanized_handle()
    {
        $blueprint = (new Blueprint)->setHandle('the_blueprint_handle');

        $this->assertEquals('The blueprint handle', $blueprint->title());
    }

    #[Test]
    public function it_gets_tabs()
    {
        $blueprint = new Blueprint;
        tap($blueprint->tabs(), function ($tabs) {
            $this->assertInstanceOf(Collection::class, $tabs);
            $this->assertCount(1, $tabs);
        });

        $contents = [
            'tabs' => [
                'tab_one' => [
                    'fields' => ['one' => ['type' => 'text']],
                ],
                'tab_two' => [
                    'fields' => ['two' => ['type' => 'text']],
                ],
            ],
        ];

        $blueprint->setContents($contents);

        tap($blueprint->tabs(), function ($tabs) {
            $this->assertCount(2, $tabs);
            $this->assertEveryItemIsInstanceOf(Tab::class, $tabs);
            $this->assertEquals(['tab_one', 'tab_two'], $tabs->map->handle()->values()->all());
        });
    }

    #[Test]
    public function it_puts_top_level_fields_into_a_main_tab()
    {
        $blueprint = new Blueprint;
        $this->assertEquals(['tabs' => ['main' => ['fields' => []]]], $blueprint->contents());

        $blueprint->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
            ],
        ]);

        $this->assertEquals([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'one',
                                    'field' => ['type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_converts_top_level_sections_into_tabs()
    {
        $blueprint = new Blueprint;
        $this->assertEquals(['tabs' => ['main' => ['fields' => []]]], $blueprint->contents());

        $blueprint->setContents([
            'sections' => [
                'one' => [
                    'display' => 'One',
                    'fields' => [
                        [
                            'handle' => 'alfa',
                            'field' => ['type' => 'text'],
                        ],
                    ],
                ],
                'two' => [
                    'fields' => [
                        [
                            'handle' => 'bravo',
                            'field' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            'tabs' => [
                'one' => [
                    'display' => 'One',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'alfa',
                                    'field' => ['type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                ],
                'two' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'bravo',
                                    'field' => ['type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
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

        $blueprint = (new Blueprint)->setHandle('tabbed')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'fields' => [
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                    ],
                ],
                'tab_two' => [
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

        $this->assertTrue($blueprint->hasFieldInTab('one', 'tab_one'));
        $this->assertTrue($blueprint->hasFieldInTab('two', 'tab_two'));
        $this->assertTrue($blueprint->hasFieldInTab('three', 'tab_two'));
        $this->assertFalse($blueprint->hasFieldInTab('one', 'tab_two')); // In tab one
        $this->assertFalse($blueprint->hasFieldInTab('three', 'tab_one')); // In tab two
        $this->assertFalse($blueprint->hasFieldInTab('four', 'tab_two')); // Doesnt exist
    }

    #[Test]
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
            'tabs' => [
                'tab_one' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one',
                        ],
                    ],
                ],
                'tab_two' => [
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

    #[Test]
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
            'tabs' => [
                'tab_one' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one',
                        ],
                    ],
                ],
                'tab_two' => [
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

    #[Test]
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
                'placeholder' => null,
                'instructions' => 'Two instructions',
                'validate' => 'min:2',
            ]));

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'instructions' => 'Does stuff',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'one',
                                    'field' => 'fieldset_one.field_one',
                                ],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'two',
                                    'field' => 'fieldset_one.field_two',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'title' => 'Test',
            'handle' => 'test',
            'tabs' => [
                [
                    'display' => 'Tab one',
                    'instructions' => 'Does stuff',
                    'handle' => 'tab_one',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'display' => 'One',
                                    'hide_display' => false,
                                    'handle' => 'one',
                                    'instructions' => 'One instructions',
                                    'instructions_position' => 'above',
                                    'listable' => 'hidden',
                                    'sortable' => true,
                                    'visibility' => 'visible',
                                    'replicator_preview' => true,
                                    'duplicate' => true,
                                    'actions' => true,
                                    'type' => 'text',
                                    'validate' => 'required|min:2',
                                    'input_type' => 'text',
                                    'placeholder' => null,
                                    'default' => null,
                                    'character_limit' => 0,
                                    'autocomplete' => null,
                                    'prepend' => null,
                                    'append' => null,
                                    'antlers' => false,
                                    'component' => 'text',
                                    'prefix' => null,
                                    'required' => true,
                                    'read_only' => false, // deprecated
                                    'always_save' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'display' => 'Tab two',
                    'instructions' => null,
                    'handle' => 'tab_two',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'display' => 'Two',
                                    'hide_display' => false,
                                    'handle' => 'two',
                                    'instructions' => 'Two instructions',
                                    'instructions_position' => 'above',
                                    'listable' => 'hidden',
                                    'sortable' => true,
                                    'visibility' => 'visible',
                                    'replicator_preview' => true,
                                    'duplicate' => true,
                                    'actions' => true,
                                    'type' => 'textarea',
                                    'placeholder' => null,
                                    'validate' => 'min:2',
                                    'character_limit' => 0,
                                    'default' => null,
                                    'antlers' => false,
                                    'component' => 'textarea',
                                    'prefix' => null,
                                    'required' => false,
                                    'read_only' => false, // deprecated
                                    'always_save' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'empty' => false,
        ], $blueprint->toPublishArray());
    }

    #[Test]
    public function converts_to_array_suitable_for_rendering_prefixed_conditional_fields_in_publish_component()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('deeper_partial')
            ->andReturn((new Fieldset)->setHandle('deeper_partial')->setContents([
                'title' => 'Deeper Partial',
                'fields' => [
                    [
                        'handle' => 'two',
                        'field' => ['type' => 'text'],
                    ],
                ],
            ]));

        FieldsetRepository::shouldReceive('find')
            ->with('partial')
            ->andReturn((new Fieldset)->setHandle('partial')->setContents([
                'title' => 'Partial',
                'fields' => [
                    [
                        'handle' => 'one',
                        'field' => ['type' => 'text'],
                    ],
                    [
                        'import' => 'deeper_partial',
                        'prefix' => 'deeper_',
                    ],
                ],
            ]));

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['import' => 'partial', 'prefix' => 'nested_'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'title' => 'Test',
            'handle' => 'test',
            'tabs' => [
                [
                    'display' => 'Tab one',
                    'instructions' => null,
                    'handle' => 'tab_one',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'display' => 'Nested One',
                                    'hide_display' => false,
                                    'handle' => 'nested_one',
                                    'instructions' => null,
                                    'instructions_position' => 'above',
                                    'listable' => 'hidden',
                                    'sortable' => true,
                                    'visibility' => 'visible',
                                    'replicator_preview' => true,
                                    'duplicate' => true,
                                    'actions' => true,
                                    'type' => 'text',
                                    'input_type' => 'text',
                                    'placeholder' => null,
                                    'default' => null,
                                    'character_limit' => 0,
                                    'autocomplete' => null,
                                    'prepend' => null,
                                    'append' => null,
                                    'antlers' => false,
                                    'component' => 'text',
                                    'prefix' => 'nested_',
                                    'required' => false,
                                    'read_only' => false, // deprecated
                                    'always_save' => false,
                                ],
                                [
                                    'display' => 'Nested Deeper Two',
                                    'hide_display' => false,
                                    'handle' => 'nested_deeper_two',
                                    'instructions' => null,
                                    'instructions_position' => 'above',
                                    'listable' => 'hidden',
                                    'sortable' => true,
                                    'visibility' => 'visible',
                                    'replicator_preview' => true,
                                    'duplicate' => true,
                                    'actions' => true,
                                    'type' => 'text',
                                    'input_type' => 'text',
                                    'placeholder' => null,
                                    'default' => null,
                                    'character_limit' => 0,
                                    'autocomplete' => null,
                                    'prepend' => null,
                                    'append' => null,
                                    'antlers' => false,
                                    'component' => 'text',
                                    'prefix' => 'nested_deeper_',
                                    'required' => false,
                                    'read_only' => false, // deprecated
                                    'always_save' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'empty' => false,
        ], $blueprint->toPublishArray());
    }

    #[Test]
    public function it_saves_through_the_repository()
    {
        Event::fake();

        $blueprint = new Blueprint;

        BlueprintRepository::shouldReceive('find')->with($blueprint->handle());
        BlueprintRepository::shouldReceive('save')->with($blueprint)->once();

        $return = $blueprint->save();

        $this->assertEquals($blueprint, $return);

        Event::assertDispatched(BlueprintCreating::class, function ($event) use ($blueprint) {
            return $event->blueprint === $blueprint;
        });

        Event::assertDispatched(BlueprintSaving::class, function ($event) use ($blueprint) {
            return $event->blueprint === $blueprint;
        });

        Event::assertDispatched(BlueprintCreated::class, function ($event) use ($blueprint) {
            return $event->blueprint === $blueprint;
        });

        Event::assertDispatched(BlueprintSaved::class, function ($event) use ($blueprint) {
            return $event->blueprint === $blueprint;
        });
    }

    #[Test]
    public function it_dispatches_blueprint_created_only_once()
    {
        Event::fake();

        $blueprint = new Blueprint;

        BlueprintRepository::shouldReceive('save')->with($blueprint);
        BlueprintRepository::shouldReceive('find')->with($blueprint->handle())->times(3)->andReturn(null, $blueprint, $blueprint);

        $blueprint->save();
        $blueprint->save();
        $blueprint->save();

        Event::assertDispatched(BlueprintSaved::class, 3);
        Event::assertDispatched(BlueprintCreated::class, 1);
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $blueprint = new Blueprint;

        BlueprintRepository::shouldReceive('find')->with($blueprint->handle());
        BlueprintRepository::shouldReceive('save')->with($blueprint)->once();

        $return = $blueprint->saveQuietly();

        $this->assertEquals($blueprint, $return);

        Event::assertNotDispatched(BlueprintCreating::class);
        Event::assertNotDispatched(BlueprintSaving::class);
        Event::assertNotDispatched(BlueprintSaved::class);
        Event::assertNotDispatched(BlueprintCreated::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_blueprint_doesnt_save()
    {
        Event::fake([BlueprintCreated::class]);

        Event::listen(BlueprintCreating::class, function () {
            return false;
        });

        $blueprint = new Blueprint;

        $return = $blueprint->save();

        $this->assertFalse($return);
        Event::assertNotDispatched(BlueprintCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_blueprint_doesnt_save()
    {
        Event::fake([BlueprintSaved::class]);

        Event::listen(BlueprintSaving::class, function () {
            return false;
        });

        $blueprint = new Blueprint;

        BlueprintRepository::shouldReceive('find')->with($blueprint->handle());
        BlueprintRepository::shouldReceive('save')->with($blueprint)->once();

        $return = $blueprint->saveQuietly();

        $this->assertEquals($blueprint, $return);

        Event::assertNotDispatched(BlueprintSaved::class);
    }

    #[Test]
    public function it_ensures_a_field_exists()
    {
        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing_in_section_one', 'field' => ['type' => 'text']],
                        ],
                    ],
                    [
                        'fields' => [
                            ['handle' => 'existing_in_section_two', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('new', ['type' => 'textarea']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing_in_section_one'));
        $this->assertTrue($blueprint->hasField('existing_in_section_two'));
        $this->assertTrue($blueprint->hasField('new'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing_in_section_one', 'field' => ['type' => 'text']],
                        ],
                    ],
                    [
                        'fields' => [
                            ['handle' => 'existing_in_section_two', 'field' => ['type' => 'text']],
                            ['handle' => 'new', 'field' => ['type' => 'textarea']],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'textarea'], $blueprint->fields()->get('new')->config());
    }

    #[Test]
    public function it_ensures_a_field_exists_in_a_specific_tab()
    {
        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('new', ['type' => 'textarea'], 'tab_two');

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertTrue($blueprint->hasField('new'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
            'tab_two' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'new', 'field' => ['type' => 'textarea']],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'textarea'], $blueprint->fields()->get('new')->config());
    }

    #[Test]
    public function it_can_add_fields_multiple_times()
    {
        $blueprint = (new Blueprint)
            ->setNamespace('collections/collection_one')
            ->setHandle('blueprint_one');

        $entry = (new Entry)
            ->collection('collection_one')
            ->blueprint($blueprint);

        $blueprint->setParent($entry);

        $blueprint->ensureFieldsInTab(['field_one' => ['type' => 'text']], 'tab_one');

        $this->assertTrue($blueprint->hasField('field_one'));

        $blueprint->ensureField('field_two', ['type' => 'textarea']);

        $this->assertTrue($blueprint->hasField('field_two'));

    }

    #[Test]
    public function it_ensures_a_field_has_config()
    {
        FieldsetRepository::shouldReceive('find')->with('the_partial')->andReturn(
            (new Fieldset)->setContents(['fields' => [
                [
                    'handle' => 'the_field',
                    'field' => ['type' => 'text', 'do_not_touch_other_config' => true],
                ],
            ]])
        );

        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'title', 'field' => ['type' => 'text']],
                        ],
                    ],
                    [
                        'fields' => [
                            ['handle' => 'author', 'field' => ['type' => 'text', 'do_not_touch_other_config' => true]],
                        ],
                    ],
                ],
            ],
            'tab_two' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'content', 'field' => ['type' => 'text']],
                        ],
                    ],
                    [
                        'fields' => [
                            ['handle' => 'the_field', 'field' => 'the_partial.the_field', 'config' => ['type' => 'text', 'do_not_touch_other_config' => true]],
                        ],
                    ],
                ],
            ],
        ]]);

        $fields = $blueprint
            ->ensureFieldHasConfig('author', ['visibility' => 'read_only'])
            ->ensureFieldHasConfig('the_field', ['visibility' => 'read_only'])
            ->fields();

        $this->assertEquals(['type' => 'text'], $fields->get('title')->config());
        $this->assertEquals(['type' => 'text'], $fields->get('content')->config());

        $expectedConfig = [
            'type' => 'text',
            'do_not_touch_other_config' => true,
            'visibility' => 'read_only',
        ];

        $this->assertEquals($expectedConfig, $fields->get('author')->config());
        $this->assertEquals($expectedConfig, $fields->get('the_field')->config());
    }

    // todo: duplicate or tweak above test but make the target field not in the first section.

    #[Test]
    public function it_can_ensure_an_deferred_ensured_field_has_specific_config()
    {
        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'title', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]]);

        // Let's say somewhere else in the code ensures an `author` field
        $blueprint->ensureField('author', ['type' => 'text', 'do_not_touch_other_config' => true, 'foo' => 'bar']);

        // Then later, we try to ensure that `author` field has config, we should be able to successfully modify that deferred field
        $fields = $blueprint
            ->ensureFieldHasConfig('author', ['foo' => 'baz', 'visibility' => 'read_only'])
            ->fields();

        $this->assertEquals(['type' => 'text'], $fields->get('title')->config());

        $expectedConfig = [
            'type' => 'text',
            'do_not_touch_other_config' => true,
            'foo' => 'baz',
            'visibility' => 'read_only',
        ];

        $this->assertEquals($expectedConfig, $fields->get('author')->config());
    }

    #[Test]
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_a_field_exists_and_it_already_exists()
    {
        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('existing', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('existing')->config());
    }

    #[Test]
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_prepended_a_field_exists_and_it_already_exists()
    {
        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'first', 'field' => ['type' => 'text']],
                            ['handle' => 'existing', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureFieldPrepended('existing', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'first', 'field' => ['type' => 'text']],
                            ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('existing')->config());
    }

    #[Test]
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_a_field_exists_and_it_already_exists_in_a_specific_tab()
    {
        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('existing', ['type' => 'textarea', 'foo' => 'bar'], 'another_tab');

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('existing'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('existing')->config());
    }

    #[Test]
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

        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'from_partial', 'field' => 'the_partial.the_field'],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('from_partial', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('from_partial'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'from_partial', 'field' => 'the_partial.the_field', 'config' => ['foo' => 'bar']],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('from_partial')->config());
    }

    #[Test]
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

        $blueprint = (new Blueprint)->setContents(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            ['import' => 'the_partial'],
                        ],
                    ],
                ],
            ],
        ]]);

        $return = $blueprint->ensureField('one', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('one'));
        $this->assertEquals(['tabs' => [
            'tab_one' => [
                'sections' => [
                    [
                        'fields' => [
                            [
                                'import' => 'the_partial',
                                'config' => [
                                    'one' => ['foo' => 'bar'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]], $blueprint->contents());
        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $blueprint->fields()->get('one')->config());
    }

    #[Test]
    public function it_ensures_a_field_exists_if_it_doesnt_and_prepends_it()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'two', 'field' => ['type' => 'text']],
                            ],
                        ],
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

    #[Test]
    public function it_ensures_a_field_exists_in_a_given_tab_if_it_doesnt_exist_at_all()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'two', 'field' => ['type' => 'text', 'foo' => 'bar']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertFalse($blueprint->hasField('three'));
        $this->assertEquals(2, $blueprint->tabs()->count());

        $return = $blueprint
            // field "three" doesnt exist, so it will be added to a new "tab_three" tab
            ->ensureField('three', ['type' => 'textarea'], 'tab_three')
            // field "two" exists, even though its in a different tab than the one requested, so the config is merged
            ->ensureField('two', ['type' => 'textarea'], 'tab_three');

        $this->assertEquals($blueprint, $return);
        $this->assertTrue($blueprint->hasField('three'));
        $this->assertEquals(3, $blueprint->tabs()->count());
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

    #[Test]
    public function it_removes_a_field()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'two', 'field' => ['type' => 'text']],
                                ['handle' => 'three', 'field' => ['type' => 'text']],
                            ],
                        ],
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

    #[Test]
    public function it_removes_a_field_from_a_specific_tab()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                                ['handle' => 'two', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'three', 'field' => ['type' => 'text']],
                                ['handle' => 'four', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertTrue($blueprint->hasField('three'));
        $this->assertTrue($blueprint->hasField('four'));

        $return = $blueprint
            ->removeField('one', 'tab_one')
            ->removeField('four', 'tab_one') // Doesn't exist in tab one, so it won't be removed.
            ->removeFieldFromTab('three', 'tab_two')
            ->removeFieldFromTab('two', 'tab_two') // Don't exist in tab two, so it won't be removed.
            ->removeField('seven', 'tab_one') // Ensure it doesn't error when field doesn't exist at all.
            ->removeFieldFromTab('eight', 'tab_one'); // Ensure it doesn't error when field doesn't exist at all.

        $this->assertEquals($blueprint, $return);

        $this->assertFalse($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertFalse($blueprint->hasField('three'));
        $this->assertTrue($blueprint->hasField('four'));
    }

    #[Test]
    public function it_removes_a_specific_tab()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                                ['handle' => 'two', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'three', 'field' => ['type' => 'text']],
                                ['handle' => 'four', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($blueprint->hasTab('tab_one'));
        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertTrue($blueprint->hasTab('tab_two'));
        $this->assertTrue($blueprint->hasField('three'));
        $this->assertTrue($blueprint->hasField('four'));

        $return = $blueprint->removeTab('tab_two');

        $this->assertEquals($blueprint, $return);

        $this->assertTrue($blueprint->hasTab('tab_one'));
        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertFalse($blueprint->hasTab('tab_two'));
        $this->assertFalse($blueprint->hasField('three'));
        $this->assertFalse($blueprint->hasField('four'));
    }

    #[Test]
    public function it_validates_unique_handles()
    {
        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate field [one] on blueprint [test].');

        $blueprint->fields();
    }

    #[Test]
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
            'tabs' => [
                'tab_one' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['import' => 'test'],
                            ],
                        ],
                    ],
                ],
                'tab_two' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'one', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate field [one] on blueprint [test].');

        $blueprint->fields();
    }

    #[Test]
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
                'tabs' => [
                    'tab_one' => [
                        'sections' => [
                            [
                                'fields' => [
                                    ['import' => 'test', 'prefix' => 'first_'],
                                    ['import' => 'test', 'prefix' => 'second_'],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->ensureField('test', ['type' => 'text']); // This was screwing with multiple imports of the same fieldset.

        $this->assertTrue($blueprint->hasField('first_one'));
        $this->assertTrue($blueprint->hasField('second_one'));
    }

    #[Test]
    public function it_gets_the_handle_when_casting_to_a_string()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertEquals('test', (string) $blueprint);
    }

    #[Test]
    public function it_augments()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertInstanceof(Augmentable::class, $blueprint);
        $this->assertEquals([
            'title' => 'Test',
            'handle' => 'test',
        ], $blueprint->toAugmentedArray());
    }

    #[Test]
    public function it_augments_in_the_parser()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertEquals('test', Facades\Antlers::parse('{{ blueprint }}', ['blueprint' => $blueprint]));

        $this->assertEquals('test Test', Facades\Antlers::parse('{{ blueprint }}{{ handle }} {{ title }}{{ /blueprint }}', ['blueprint' => $blueprint]));
    }

    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $blueprint
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $blueprint->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $blueprint[$key]));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $blueprint = (new Blueprint)->setHandle('test');

        $this->assertInstanceOf(Arrayable::class, $blueprint);

        collect($blueprint->toArray())
            ->each(fn ($value, $key) => $this->assertEquals($value, $blueprint->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value, $blueprint[$key]));
    }

    #[Test]
    public function it_resolves_itself_to_a_queryable_value()
    {
        $blueprint = (new Blueprint)->setHandle('test');
        $this->assertInstanceOf(QueryableValue::class, $blueprint);
        $this->assertEquals('test', $blueprint->toQueryableValue());
    }

    #[Test]
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $blueprint = (new Blueprint)->setHandle('test');

        $blueprint->delete();

        Event::assertDispatched(BlueprintDeleting::class, function ($event) use ($blueprint) {
            return $event->blueprint === $blueprint;
        });
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        Facades\Blueprint::spy();
        Event::fake([BlueprintDeleted::class]);

        Event::listen(BlueprintDeleting::class, function () {
            return false;
        });

        $blueprint = (new Blueprint)->setHandle('test');
        $return = $blueprint->delete();

        $this->assertFalse($return);
        Facades\Blueprint::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(BlueprintDeleted::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $blueprint = (new Blueprint)->setHandle('test');

        $return = $blueprint->deleteQuietly();

        Event::assertNotDispatched(BlueprintDeleting::class);
        Event::assertNotDispatched(BlueprintDeleted::class);

        $this->assertTrue($return);
    }
}
