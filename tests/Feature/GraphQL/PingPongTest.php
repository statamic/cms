<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;

/** @group graphql */
class PingPongTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        \Illuminate\Testing\TestResponse::macro('assertGqlData', function ($data) {
            \PHPUnit\Framework\Assert::assertEquals(['data' => $data], $this->json());
        });
    }

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
