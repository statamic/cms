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
    public function it_gets_the_display()
    {
        $this->assertNull((new Section([]))->display());

        $this->assertEquals('Test', (new Section(['display' => 'Test']))->display());
    }

    /** @test */
    public function it_gets_the_instructions()
    {
        $this->assertNull((new Section([]))->instructions());

        $this->assertEquals('Test', (new Section(['instructions' => 'Test']))->instructions());
    }

    /** @test */
    public function it_gets_contents()
    {
        $section = new Section($contents = ['foo' => 'bar']);

        $this->assertEquals($contents, $section->contents());
    }

    /** @test */
    public function it_gets_fields()
    {
        $section = new Section([]);
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

        $section = new Section([
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

        $section = new Section([
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
        ], $section->toPublishArray());
    }
}
