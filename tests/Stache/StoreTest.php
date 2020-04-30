<?php

namespace Tests\Stache;

use Statamic\Stache\Stache;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class StoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = new Stache;

        $this->store = new TestStore($stache);
    }

    /** @test */
    public function it_forces_a_trailing_slash_when_setting_the_directory()
    {
        $this->assertNull($this->store->directory());

        $return = $this->store->directory('/path/to/directory');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('/path/to/directory/', $this->store->directory());

        // Check the value of the property to make sure the property was set with
        // the slash, and that ->directory() isn't just appending it.
        $property = (new \ReflectionClass($this->store))->getProperty('directory');
        $property->setAccessible(true);
        $this->assertEquals('/path/to/directory/', $property->getValue($this->store));
    }
}

class TestStore extends Store
{
    public function getItem($key)
    {
    }
}
