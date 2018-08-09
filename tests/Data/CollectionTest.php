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
}
