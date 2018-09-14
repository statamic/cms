<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldRepository;

class FieldsTest extends TestCase
{
    /** @test */
    function it_converts_to_a_collection()
    {
        $fields = new Fields;

        tap($fields->all(), function ($items) {
            $this->assertInstanceOf(Collection::class, $items);
            $this->assertCount(0, $items);
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $fields->setItems([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ]
        ]);

        tap($fields->all(), function ($items) {
            $this->assertCount(2, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
            $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
        });
    }

    /** @test */
    function it_merges_with_other_fields()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ]
        ]);

        $second = new Fields([
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ]
        ]);

        $merged = $fields->merge($second);

        $this->assertCount(1, $fields->all());
        $this->assertCount(2, $items = $merged->all());
        $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
        $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
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
                'validate' => 'required',
            ]));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_two', [
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions'
            ]));

        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ]
        ]);

        $this->assertEquals([
            [
                'handle' => 'one',
                'type' => 'text',
                'display' => 'One',
                'instructions' => 'One instructions',
                'required' => true
            ],
            [
                'handle' => 'two',
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'required' => false
            ]
        ], $fields->toPublishArray());
    }
}
