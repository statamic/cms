<?php

namespace Tests\Composer;

use Facades\GuzzleHttp\Client;
use Facades\Statamic\Version;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Updater\CoreChangelog;
use Tests\TestCase;

class CoreChangelogTest extends TestCase
{
    use ChangelogTests;

    protected $shouldFakeVersion = false;

    protected function changelog()
    {
        Version::shouldReceive('get')->andReturn('1.0.1');

        return new CoreChangelog;
    }

    #[Test]
    public function it_returns_an_empty_changelog_when_the_marketplace_times_out()
    {
        Client::shouldReceive('request')
            ->withArgs(fn ($method, $uri, $options) => $options['timeout'] === 5)
            ->once()
            ->andThrow(new ConnectException('', new Request('GET', '/api/v1/marketplace/packages/statamic/cms/releases')));

        $this->assertCount(0, $this->changelog()->get());
    }
}
