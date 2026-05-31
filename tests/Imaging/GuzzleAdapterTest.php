<?php

namespace Tests\Imaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\InvalidRemoteUrlException;
use Statamic\Imaging\GuzzleAdapter;
use Statamic\Imaging\RemoteUrlValidator;
use Tests\TestCase;

class GuzzleAdapterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->bind(RemoteUrlValidator::class, function () {
            return new RemoteUrlValidator(function ($host) {
                return match ($host) {
                    'example.com' => [['ip' => '93.184.216.34']],
                    'cdn.example.com' => [['ip' => '93.184.216.35']],
                    default => [],
                };
            });
        });
    }

    #[Test]
    public function it_pins_the_connection_to_the_validated_ip()
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')->once()->andReturnUsing(function ($method, $url, $options) {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/foo.jpg', $url);
            $this->assertFalse($options['allow_redirects']);
            $this->assertSame(['example.com:443:93.184.216.34'], $options['curl'][CURLOPT_RESOLVE]);

            return new Response(200, [], 'image-bytes');
        });

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->assertSame('image-bytes', $adapter->read('foo.jpg'));
    }

    #[Test]
    public function it_pins_the_connection_even_when_dns_would_rebind_after_validation()
    {
        // Simulates the DNS rebinding attack: validation sees a public IP, but a
        // second lookup at connection time would resolve to localhost. Because we
        // pin the validated IP via CURLOPT_RESOLVE, the connection can never use
        // the rebound address — curl is told exactly which IP to connect to.
        $rebindingResolver = function () {
            static $calls = 0;

            return [['ip' => ++$calls === 1 ? '93.184.216.34' : '127.0.0.1']];
        };

        $this->app->bind(RemoteUrlValidator::class, fn () => new RemoteUrlValidator($rebindingResolver));

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')->once()->andReturnUsing(function ($method, $url, $options) {
            // The pinned IP is the public one resolved during validation, not the rebound one.
            $this->assertSame(['example.com:443:93.184.216.34'], $options['curl'][CURLOPT_RESOLVE]);

            return new Response(200, [], 'image-bytes');
        });

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->assertSame('image-bytes', $adapter->read('foo.jpg'));
    }

    #[Test]
    public function it_follows_redirects_and_pins_every_hop()
    {
        $client = Mockery::mock(ClientInterface::class);

        $client->shouldReceive('request')->once()
            ->with('GET', 'https://example.com/foo.jpg', Mockery::on(function ($options) {
                return $options['curl'][CURLOPT_RESOLVE] === ['example.com:443:93.184.216.34'];
            }))
            ->andReturn(new Response(302, ['Location' => 'https://cdn.example.com/foo.jpg']));

        $client->shouldReceive('request')->once()
            ->with('GET', 'https://cdn.example.com/foo.jpg', Mockery::on(function ($options) {
                return $options['curl'][CURLOPT_RESOLVE] === ['cdn.example.com:443:93.184.216.35'];
            }))
            ->andReturn(new Response(200, [], 'image-bytes'));

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->assertSame('image-bytes', $adapter->read('foo.jpg'));
    }

    #[Test]
    public function it_blocks_redirects_to_non_public_destinations()
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')->once()->andReturn(
            new Response(302, ['Location' => 'http://169.254.169.254/latest/meta-data/'])
        );

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $adapter->read('foo.jpg');
    }

    #[Test]
    public function it_refuses_to_fetch_when_curl_is_unavailable()
    {
        // Without curl the connection can't be pinned to the validated IP, so rather
        // than silently fall back to a rebindable request we refuse to fetch at all.
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldNotReceive('request');

        $adapter = new class('https://example.com', $client) extends GuzzleAdapter
        {
            protected function supportsConnectionPinning(): bool
            {
                return false;
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('curl PHP extension is required');

        $adapter->read('foo.jpg');
    }

    #[Test]
    public function it_stops_following_redirects_after_the_limit()
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')
            ->andReturn(new Response(302, ['Location' => 'https://example.com/foo.jpg']));

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Too many redirects.');

        $adapter->read('foo.jpg');
    }
}
