<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Section;
use Tests\TestCase;

class SectionTest extends TestCase
{
    /** @test */
    public function it_gets_the_handle()
    {
        $section = new Section('test');

        $this->assertEquals('test', $section->handle());
    }

    /** @test */
    public function it_gets_contents()
    {
        $section = new Section('test');
        $this->assertEquals([], $section->contents());

        $contents = [
            'fields' => ['one' => ['type' => 'text']],
        ];

        $return = $section->setContents($contents);

        $this->assertEquals($section, $return);
        $this->assertEquals($contents, $section->contents());
    }

    /** @test */
    public function it_gets_the_display_text()
    {
        $section = (new Section('test'))->setContents([
            'display' => 'The Display Text',
        ]);

        $this->assertEquals('The Display Text', $section->display());
    }

    /** @test */
    public function the_display_text_falls_back_to_a_humanized_handle()
    {
        $section = new Section('the_section_handle');

        $this->assertEquals('The section handle', $section->display());
    }

    /** @test */
    public function it_gets_fields()
    {
        $section = new Section('test');
        tap($section->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $section->setContents($contents = [
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

        tap($section->fields(), function ($fields) {
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

        $section = (new Section('test'))->setContents([
            'display' => 'Test Section',
            'instructions' => 'Does stuff',
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

        $this->assertEquals([
            'display' => 'Test Section',
            'handle' => 'test',
            'instructions' => 'Does stuff',
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
                ],
            ],
        ], $section->toPublishArray());
    }
}
