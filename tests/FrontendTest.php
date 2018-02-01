<?php

namespace Tests;

class FrontendTest extends TestCase
{
    /** @test */
    function vanity_routes_get_redirected()
    {
        config(['statamic.routes.vanity' => ['/foo' => '/foobar']]);

        $this->get('/foo')->assertStatus(302)->assertRedirect('/foobar');
    }

    /** @test */
    function permanent_redirects_get_redirected()
    {
        config(['statamic.routes.redirect' => ['/foo' => '/foobar']]);

        $this->get('/foo')->assertStatus(301)->assertRedirect('/foobar');
    }
}