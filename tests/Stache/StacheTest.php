<?php

namespace Tests\Stache;

use Statamic\Stache\Stache;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Statamic\Stache\Stores\CollectionsStore;

class StacheTest extends TestCase
{
    public function setUp()
    {
        $this->stache = new Stache;
    }

    /** @test */
    function it_can_be_heated_and_cooled()
    {
        $this->assertTrue($this->stache->isCold());
        $this->assertFalse($this->stache->isWarm());

        $this->stache->heat();

        $this->assertTrue($this->stache->isWarm());
        $this->assertFalse($this->stache->isCold());

        $this->stache->cool();

        $this->assertFalse($this->stache->isWarm());
        $this->assertTrue($this->stache->isCold());
    }

    /** @test */
    function sites_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->sites());

        $return = $this->stache->sites(['one', 'two']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->sites());
        $this->assertEquals(['one', 'two'], $this->stache->sites()->all());
    }

    /** @test */
    function default_site_can_be_retrieved()
    {
        $this->stache->sites(['foo', 'bar']);

        $this->assertEquals('foo', $this->stache->defaultSite());
    }

    /** @test */
    function meta_data_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->meta());

        $return = $this->stache->meta(['foo', 'bar']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->meta());
        $this->assertEquals(['foo', 'bar'], $this->stache->meta()->all());
    }

    /** @test */
    function cached_keys_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->keys());

        $return = $this->stache->keys(['foo', 'bar']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->keys());
        $this->assertEquals(['foo', 'bar'], $this->stache->keys()->all());
    }

    /** @test */
    function config_data_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->config());

        $return = $this->stache->config(['foo', 'bar']);

        $this->assertEquals($this->stache, $return);
        $this->assertEquals(['foo', 'bar'], $this->stache->config());
    }

    /** @test */
    function stores_can_be_registered()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $return = $this->stache->registerStore(new CollectionsStore($this->stache));

        $this->assertEquals($this->stache, $return);
        tap($this->stache->stores(), function ($stores) {
            $this->assertEquals(1, $stores->count());
            $this->assertEquals('collections', $stores->keys()->first());
            $this->assertInstanceOf(CollectionsStore::class, $stores->first());
            $this->assertInstanceOf(CollectionsStore::class, $this->stache->store('collections'));
        });
    }
}
