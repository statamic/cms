<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Bootstrapper;

class BootstrapperTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            Bootstrapper::CONFIG_UPDATE_EVERY_REQUEST_KEY => true,
            Bootstrapper::CONFIG_GLIDE_ROUTE_KEY => '/img'
        ]);

        $this->bootstrapper = new Bootstrapper;
    }

    /** @test */
    function it_should_update_by_default()
    {
        $this->assertTrue($this->bootstrapper->shouldUpdate());
    }

    /** @test */
    function it_should_not_update_if_configured_not_to()
    {
        config([Bootstrapper::CONFIG_UPDATE_EVERY_REQUEST_KEY => false]);

        $this->assertFalse($this->bootstrapper->shouldUpdate());
    }

    /** @test */
    function it_should_not_update_if_its_a_glide_route()
    {
        $this->get('/img/testing');
        $this->assertFalse($this->bootstrapper->shouldUpdate());

        // Make sure the slash (or absence of a slash) is taken into account.
        $this->get('/imgur');
        $this->assertTrue($this->bootstrapper->shouldUpdate());
    }
}
