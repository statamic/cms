<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Mockery;
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
}
