<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Tab;
use Tests\TestCase;

class TabTest extends TestCase
{
    #[Test]
    public function it_gets_the_handle()
    {
        $tab = new Tab('test');

        $this->assertEquals('test', $tab->handle());
    }

    #[Test]
    public function it_gets_contents()
    {
        $tab = new Tab('test');
        $this->assertEquals([], $tab->contents());

        $contents = [
            'fields' => ['one' => ['type' => 'text']],
        ];

        $return = $tab->setContents($contents);

        $this->assertEquals($tab, $return);
        $this->assertEquals($contents, $tab->contents());
    }

    #[Test]
    public function it_gets_the_display_text()
    {
        $tab = (new Tab('test'))->setContents([
            'display' => 'The Display Text',
        ]);

        $this->assertEquals('The Display Text', $tab->display());
    }

    #[Test]
    public function the_display_text_falls_back_to_a_humanized_handle()
    {
        $tab = new Tab('the_tab_handle');

        $this->assertEquals('The tab handle', $tab->display());
    }

    #[Test]
    public function it_gets_fields()
    {
        $tab = new Tab('test');
        tap($tab->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $tab->setContents($contents = [
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => 'fieldset_one.field_one',
                ],
                [
                    'handle' => 'two',
                    'field' => 'fieldset_one.field_two',
                ],
            ],
        ]);

        tap($tab->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            tap($fields->all(), function ($items) {
                $this->assertCount(2, $items->all());
                $this->assertEveryItemIsInstanceOf(Field::class, $items);
                $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
                $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
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
                'instructions' => 'Two instructions',
                'validate' => 'min:2',
            ]));

        $tab = (new Tab('test'))->setContents([
            'display' => 'Test Tab',
            'instructions' => 'Does stuff',
            'sections' => [
                [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one',
                        ],
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'display' => 'Test Tab',
            'instructions' => 'Does stuff',
            'handle' => 'test',
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
                            'validate' => 'min:2',
                            'placeholder' => null,
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
        ], $tab->toPublishArray());
    }
}
