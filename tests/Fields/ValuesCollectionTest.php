<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\AugmentedCollection;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Fields\ValuesCollection;
use Tests\TestCase;

class ValuesCollectionTest extends TestCase
{
    /** @test */
    public function it_converts_to_a_string()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('__toString')->andReturn('the collection would return a json string');

        $values = new ValuesCollection($collection);

        $this->assertEquals('the collection would return a json string', (string) $values);
    }

    /** @test */
    public function it_converts_to_json()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('jsonSerialize')->andReturn(['test' => 'the collection would return an array']);

        $values = new ValuesCollection($collection);

        $this->assertEquals('{"test":"the collection would return an array"}', json_encode($values));
    }

    /** @test */
    public function macro_is_registered_with_arrays()
    {
        $one = [
            'title' => 'plain title one',
            'field' => 'raw field one',
        ];

        $two = [
            'title' => 'plain title two',
            'field' => 'raw field two',
        ];

        $collection = Collection::make([$one, $two]);

        $values = $collection->toValuesCollection();

        $this->assertInstanceOf(ValuesCollection::class, $values);
        $this->assertEveryItemIsInstanceOf(Values::class, $values);
        $this->assertEquals('plain title one', $values->first()->title);
        $this->assertEquals('raw field one', $values->first()->field);
        $this->assertEquals('plain title two', $values->last()->title);
        $this->assertEquals('raw field two', $values->last()->field);
    }

    /** @test */
    public function macro_is_registered_with_collections()
    {
        $one = collect([
            'title' => 'plain title one',
            'field' => 'raw field one',
        ]);

        $two = collect([
            'title' => 'plain title two',
            'field' => 'raw field two',
        ]);

        $collection = Collection::make([$one, $two]);

        $values = $collection->toValuesCollection();

        $this->assertInstanceOf(ValuesCollection::class, $values);
        $this->assertEveryItemIsInstanceOf(Values::class, $values);
        $this->assertEquals('plain title one', $values->first()->title);
        $this->assertEquals('raw field one', $values->first()->field);
        $this->assertEquals('plain title two', $values->last()->title);
        $this->assertEquals('raw field two', $values->last()->field);
    }

    /** @test */
    public function macro_is_registered_with_augmentables()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return str_replace('raw', 'augmented', $value);
            }
        };

        $one = Mockery::mock(Augmentable::class);
        $one->shouldReceive('toAugmentedCollection')->once()->andReturn(new AugmentedCollection([
            'title' => 'plain title one',
            'field' => new Value('raw field one', null, $fieldtype),
        ]));

        $two = Mockery::mock(Augmentable::class);
        $two->shouldReceive('toAugmentedCollection')->once()->andReturn(new AugmentedCollection([
            'title' => 'plain title two',
            'field' => new Value('raw field two', null, $fieldtype),
        ]));

        $collection = Collection::make([$one, $two]);

        $values = $collection->toValuesCollection();

        $this->assertInstanceOf(ValuesCollection::class, $values);
        $this->assertEveryItemIsInstanceOf(Values::class, $values);
        $this->assertEquals('plain title one', $values->first()->title);
        $this->assertEquals('augmented field one', $values->first()->field);
        $this->assertEquals('plain title two', $values->last()->title);
        $this->assertEquals('augmented field two', $values->last()->field);
    }
}
