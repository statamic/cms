<?php

namespace Tests\Marketplace;

use Facades\Statamic\Marketplace\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Marketplace\Marketplace;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (fn () => static::$packagesCache = [])->call(new Marketplace);
    }

    #[Test]
    public function it_gets_packages()
    {
        Client::shouldReceive('post')->once()->andReturn(['data' => ['foo/bar' => ['id' => 1]]]);

        $packages = (new Marketplace)->packages(['foo/bar']);

        $this->assertEquals(['foo/bar' => ['id' => 1]], $packages->all());
    }

    #[Test]
    public function it_returns_no_packages_without_caching_when_the_client_returns_nothing()
    {
        Client::shouldReceive('post')->once()->andReturnNull();

        $packages = (new Marketplace)->packages(['foo/bar']);

        $this->assertInstanceOf(Collection::class, $packages);
        $this->assertCount(0, $packages);
        $this->assertFalse(cache()->has('marketplace-packages-'.md5(json_encode(['foo/bar']))));
    }

    #[Test]
    public function it_caches_no_packages_when_the_request_fails()
    {
        Client::shouldReceive('post')->once()->andThrow(new ConnectException('', new Request('POST', '/')));

        $packages = (new Marketplace)->packages(['foo/bar']);

        $this->assertCount(0, $packages);
        $this->assertTrue(cache()->has('marketplace-packages-'.md5(json_encode(['foo/bar']))));
    }

    #[Test]
    public function it_returns_no_themes_without_caching_when_the_client_returns_nothing()
    {
        Client::shouldReceive('get')->twice()->andReturnNull();

        $marketplace = new Marketplace;

        $this->assertCount(0, $marketplace->themes());
        $this->assertCount(0, $marketplace->themes());
    }

    #[Test]
    public function it_caches_no_themes_when_the_request_fails()
    {
        Client::shouldReceive('get')->once()->andThrow(new ConnectException('', new Request('GET', '/')));

        $marketplace = new Marketplace;

        $this->assertCount(0, $marketplace->themes());
        $this->assertCount(0, $marketplace->themes());
    }

    #[Test]
    public function it_returns_no_releases_without_caching_when_the_client_returns_nothing()
    {
        Client::shouldReceive('get')->twice()->andReturnNull();

        $marketplace = new Marketplace;

        $this->assertEquals(['data' => collect(), 'meta' => null], $marketplace->releases('foo/bar'));
        $this->assertEquals(['data' => collect(), 'meta' => null], $marketplace->releases('foo/bar'));
    }
}
