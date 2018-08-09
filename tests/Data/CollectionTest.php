<?php

namespace Tests\Data;

use Tests\TestCase;
use Statamic\Data\Entries\Collection;
use Statamic\API\Collection as CollectionAPI;

class CollectionTest extends TestCase
{
    /** @test */
    function it_gets_the_order()
    {
        tap(new Collection, function ($collection) {
            $collection->data([]);
            $this->assertEquals('alphabetical', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'alphabetical']);
            $this->assertEquals('alphabetical', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'alpha']);
            $this->assertEquals('alphabetical', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'date']);
            $this->assertEquals('date', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'number']);
            $this->assertEquals('number', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'numeric']);
            $this->assertEquals('number', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'numbers']);
            $this->assertEquals('number', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'numerical']);
            $this->assertEquals('number', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'numbered']);
            $this->assertEquals('number', $collection->order());
        });

        tap(new Collection, function ($collection) {
            $collection->data(['order' => 'invalid']);
            $this->assertEquals('alphabetical', $collection->order());
        });
    }

    /** @test */
    function it_puts_the_route_in_the_data()
    {
        $collection = new Collection;
        $collection->data(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $collection->data());
        $this->assertNull($collection->route());

        $return = $collection->route('{slug}');

        $this->assertEquals(['foo' => 'bar', 'route' => '{slug}'], $collection->data());
        $this->assertEquals('{slug}', $collection->route());
        $this->assertEquals($collection, $return);
    }
}
