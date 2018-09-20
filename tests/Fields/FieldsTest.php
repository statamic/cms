<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Extend\Fieldtype;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldtypeRepository;

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
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'text']);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'textarea']);
            });

        $fields->setItems([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ],
            [
                'handle' => 'three',
                'field' => [
                    'type' => 'textarea',
                ]
            ]
        ]);

        tap($fields->all(), function ($items) {
            $this->assertCount(3, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $this->assertEquals(['one', 'two', 'three'], $items->map->handle()->values()->all());
            $this->assertEquals(['text', 'textarea', 'textarea'], $items->map->type()->values()->all());
        });
    }

    /** @test */
    function it_throws_an_exception_when_an_invalid_field_reference_is_encountered()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Field foo.bar not found.');
        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturnNull();

        (new Fields)->setItems([
            [
                'handle' => 'test',
                'field' => 'foo.bar'
            ]
        ]);
    }

    /** @test */
    function it_merges_with_other_fields()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'text']);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'textarea']);
            });

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
            ->andReturnUsing(function () {
                return new Field('field_one', [
                    'type' => 'text',
                    'display' => 'One',
                    'instructions' => 'One instructions',
                    'validate' => 'required',
                ]);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_two', [
                    'type' => 'textarea',
                    'display' => 'Two',
                    'instructions' => 'Two instructions'
                ]);
            });

        $fields = new Fields([
            'one' => [ // use keys to ensure they get stripped out
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            'two' => [
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

    /** @test */
    function it_adds_values_to_fields()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', []);
        });

        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', []);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values());

        $return = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals($fields, $return);
        $this->assertEquals(['one' => 'foo', 'two' => 'bar'], $fields->values());
    }

    /** @test */
    function it_processes_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function process($data) {
                return $data . ' processed';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values());

        $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals([
            'one' => 'foo processed',
            'two' => 'bar processed'
        ], $fields->process()->values());
    }

    /** @test */
    function it_preprocesses_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function preProcess($data) {
                return $data . ' preprocessed';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values());

        $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals([
            'one' => 'foo preprocessed',
            'two' => 'bar preprocessed'
        ], $fields->preProcess()->values());
    }
}
