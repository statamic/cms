<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\BasicStore;

class BasicStoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->stache = (new Stache)->sites(['en', 'fr']);

        $this->store = new TestBasicStore;
    }

    /** @test */
    function it_gets_an_item_by_key()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_gets_an_item_by_path()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_forgets_an_item_by_key()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_saves_an_item()
    {
        $this->markTestIncomplete();
    }
}

class TestBasicStore extends BasicStore
{
    public function key()
    {
        return 'test';
    }

    public function makeItemFromFile($path, $contents)
    {

    }
}
