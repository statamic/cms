<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Tab;
use Tests\TestCase;

class TabTest extends TestCase
{
    /** @test */
    public function it_gets_the_handle()
    {
        $tab = new Tab('test');

        $this->assertEquals('test', $tab->handle());
    }

    /** @test */
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

    /** @test */
    public function it_gets_the_display_text()
    {
        $tab = (new Tab('test'))->setContents([
            'display' => 'The Display Text',
        ]);

        $this->assertEquals('The Display Text', $tab->display());
    }

    /** @test */
    public function the_display_text_falls_back_to_a_humanized_handle()
    {
        $tab = new Tab('the_tab_handle');

        $this->assertEquals('The tab handle', $tab->display());
    }

    /** @test */
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

        $this->assertEquals([
            'display' => 'Test Tab',
            'handle' => 'test',
            'instructions' => 'Does stuff',
            'sections' => [
                [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'prefix' => null,
                            'type' => 'text',
                            'display' => 'One',
                            'instructions' => 'One instructions',
                            'required' => true,
                            'validate' => 'required|min:2',
                            'component' => 'text',
                            'placeholder' => null,
                            'input_type' => 'text',
                            'character_limit' => 0,
                            'prepend' => null,
                            'append' => null,
                            'antlers' => false,
                            'default' => null,
                            'visibility' => 'visible',
                            'read_only' => false, // deprecated
                            'always_save' => false,
                            'autocomplete' => null,
                        ],
                        [
                            'handle' => 'two',
                            'prefix' => null,
                            'type' => 'textarea',
                            'display' => 'Two',
                            'instructions' => 'Two instructions',
                            'required' => false,
                            'validate' => 'min:2',
                            'character_limit' => null,
                            'component' => 'textarea',
                            'antlers' => false,
                            'placeholder' => null,
                            'default' => null,
                            'visibility' => 'visible',
                            'read_only' => false, // deprecated
                            'always_save' => false,
                        ],
                    ],
                ],
            ],
        ], $tab->toPublishArray());
    }
}
