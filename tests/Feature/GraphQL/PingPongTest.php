<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;

/** @group graphql */
class PingPongTest extends TestCase
{
    /** @test */
    public function it_pongs_when_pinged()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertOk()
            ->assertExactJson(['data' => ['ping' => 'pong']]);
    }
}
