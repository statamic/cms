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
            ->withToken('invalid')
            ->postJson('/graphql', ['query' => '{ping}'])
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_cant_authenticate_without_auth_token()
    {
        Config::set('statamic.graphql.auth_token', 'foobar');

        $this
            ->postJson('/graphql', ['query' => '{ping}'])
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_returns_the_same_json_response_when_debug_mode_is_enabled()
    {
        Config::set('app.debug', true);
        Config::set('statamic.graphql.auth_token', 'foobar');

        $this
            ->postJson('/graphql', ['query' => '{ping}'])
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_returns_json_even_when_html_is_requested()
    {
        Config::set('statamic.graphql.auth_token', 'foobar');

        $this
            ->withHeader('Accept', 'text/html')
            ->post('/graphql', ['query' => '{ping}'])
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
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
