<?php

namespace Tests\Feature\GraphQL;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\GraphQL\Queries\PingQuery;
use Tests\TestCase;

#[Group('graphql')]
class QueryAuthorizationTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        PingQuery::auth(null);
    }

    #[Test]
    public function it_authorizes_by_default()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['ping' => 'pong']]);
    }

    #[Test]
    public function it_provides_custom_passing_authorization_logic()
    {
        PingQuery::auth(function () {
            return true;
        });

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['ping' => 'pong']]);
    }

    #[Test]
    public function it_provides_custom_failing_authorization_logic()
    {
        PingQuery::auth(function ($a, $b, $c, $d, $e) {
            return false;
        });

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertGqlUnauthorized()
            ->assertJson(['data' => ['ping' => null]]);
    }
}
