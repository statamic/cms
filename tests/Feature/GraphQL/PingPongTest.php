<?php

namespace Tests\Feature\GraphQL;

/** @group graphql */
class PingPongTest extends GraphQLTestCase
{
    /** @test */
    public function it_pongs_when_pinged()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertOk()
            ->assertGqlData(['ping' => 'pong']);
    }
}
