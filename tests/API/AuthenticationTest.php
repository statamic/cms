<?php

namespace Tests\API;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Config::set('statamic.api.enabled', true);
        Facades\Config::set('statamic.api.resources.collections', true);

        $this->collection = Facades\Collection::make('articles')->save();
    }

    #[Test]
    public function it_can_authenticate_using_auth_token()
    {
        Facades\Config::set('statamic.api.auth_token', 'foobar');

        $this
            ->withToken('foobar')
            ->getJson('/api/collections/articles/entries')
            ->assertOk();
    }

    #[Test]
    public function it_cant_authenticate_with_invalid_auth_token()
    {
        Facades\Config::set('statamic.api.auth_token', 'foobar');

        $this
            ->withToken('invalid')
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_cant_authenticate_without_auth_token()
    {
        Facades\Config::set('statamic.api.auth_token', 'foobar');

        $this
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_returns_the_same_json_response_when_debug_mode_is_enabled()
    {
        Facades\Config::set('app.debug', true);
        Facades\Config::set('statamic.api.auth_token', 'foobar');

        $this
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_returns_json_for_unauthenticated_requests()
    {
        Facades\Config::set('statamic.api.auth_token', 'foobar');

        $this
            ->withHeader('Accept', 'text/html')
            ->get('/api/collections/articles/entries')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function authentication_only_required_when_auth_token_is_set()
    {
        Facades\Config::set('statamic.api.auth_token', null);

        $this
            ->getJson($url = '/api/collections/articles/entries')
            ->assertOk();

        $this
            ->get($url)
            ->assertOk();
    }
}
