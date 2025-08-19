<?php

namespace vendor\statamic\cms\tests\API;

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
    public function it_can_authenticate_using_api_token()
    {
        Facades\Config::set('statamic.api.api_token', 'foobar');

        $this
            ->withToken('foobar')
            ->getJson('/api/collections/articles/entries')
            ->assertOk();
    }

    #[Test]
    public function it_cant_authenticate_with_invalid_api_token()
    {
        Facades\Config::set('statamic.api.api_token', 'foobar');

        $this
            ->withToken('invalid')
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized();
    }

    #[Test]
    public function it_cant_authenticate_without_api_token()
    {
        Facades\Config::set('statamic.api.api_token', 'foobar');

        $this
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized();
    }

    #[Test]
    public function authentication_only_required_when_api_token_is_set()
    {
        Facades\Config::set('statamic.api.api_token', null);

        $this
            ->getJson('/api/collections/articles/entries')
            ->assertOk();
    }
}
