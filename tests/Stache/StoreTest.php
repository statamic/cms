<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\Store;

class StoreTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = new Stache;

        $this->store = new TestStore($stache);
    }

    /** @test */
    function it_gets_and_sets_the_directory()
    {
        $this->assertNull($this->store->directory());

        $return = $this->store->directory('/path/to/directory');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('/path/to/directory', $this->store->directory());
    }
}

class TestStore extends Store
{
    //
}
