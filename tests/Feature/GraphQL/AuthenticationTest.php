<?php

namespace Tests\Feature\GraphQL;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Config;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class AuthenticationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_can_authenticate_using_auth_token()
    {
        Config::set('statamic.graphql.auth_token', 'foobar');

        $this
            ->withToken('foobar')
            ->postJson('/graphql', ['query' => '{ping}'])
            ->assertOk();
    }

    #[Test]
    public function it_cant_authenticate_with_invalid_auth_token()
    {
        Config::set('statamic.graphql.auth_token', 'foobar');

        $this
            ->withToken($token = 'invalid')
            ->postJson($url = '/graphql', ['query' => '{ping}'])
            ->assertUnauthorized();

        $this
            ->withToken($token)
            ->post($url, ['query' => '{ping}'])
            ->assertUnauthorized();
    }

    #[Test]
    public function it_cant_authenticate_without_auth_token()
    {
        Config::set('statamic.graphql.auth_token', 'foobar');

        $this
            ->postJson($url = '/graphql', ['query' => '{ping}'])
            ->assertUnauthorized();

        $this
            ->post($url, ['query' => '{ping}'])
            ->assertUnauthorized();
    }

    #[Test]
    public function authentication_only_required_when_auth_token_is_set()
    {
        Config::set('statamic.graphql.auth_token', null);

        $this
            ->postJson($url = '/graphql', ['query' => '{ping}'])
            ->assertOk();

        $this
            ->post($url, ['query' => '{ping}'])
            ->assertOk();
    }

    #[Test]
    public function authenticated_responses_are_not_served_to_unauthenticated_requests()
    {
        Config::set('statamic.graphql.auth_token', 'foobar');
        Config::set('statamic.graphql.cache', ['expiry' => 60]);

        // First, make an authenticated request that gets cached
        $this
            ->withToken('foobar')
            ->postJson('/graphql', ['query' => '{ping}'])
            ->assertOk()
            ->assertJsonPath('data.ping', 'pong');

        // Now make an unauthenticated request - should get 401, not cached response
        // This verifies auth happens before caching
        $this
            ->withoutToken()
            ->postJson('/graphql', ['query' => '{ping}'])
            ->assertUnauthorized();
    }
}
