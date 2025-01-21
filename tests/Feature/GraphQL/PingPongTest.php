<?php

namespace Tests\Feature\GraphQL;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('graphql')]
class PingPongTest extends TestCase
{
    #[Test]
    public function it_pongs_when_pinged()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertOk()
            ->assertExactJson(['data' => ['ping' => 'pong']]);
    }
}
