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
            ->withToken($token = 'invalid')
            ->getJson($url = '/api/collections/articles/entries')
            ->assertUnauthorized();

        $this
            ->withToken($token)
            ->get($url)
            ->assertUnauthorized();
    }

    #[Test]
    public function it_cant_authenticate_without_auth_token()
    {
        Facades\Config::set('statamic.api.auth_token', 'foobar');

        $this
            ->getJson($url = '/api/collections/articles/entries')
            ->assertUnauthorized();

        $this
            ->get($url)
            ->assertUnauthorized();
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
