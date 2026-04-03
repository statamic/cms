<?php

namespace Tests\Imaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
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
                    default => [],
                };
            });
        });
    }

    #[Test]
    public function it_allows_redirects_when_every_hop_is_public()
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('get')->once()->andReturnUsing(function ($url, $options) {
            $this->assertEquals('https://example.com/foo.jpg', $url);
            $this->assertArrayHasKey('allow_redirects', $options);
            $this->assertArrayHasKey('on_redirect', $options['allow_redirects']);

            $options['allow_redirects']['on_redirect'](
                new Request('GET', 'https://example.com/foo.jpg'),
                new Response(302, ['Location' => 'https://example.com/redirected/foo.jpg']),
                new Uri('https://example.com/redirected/foo.jpg')
            );

            return new Response(200, [], 'image-bytes');
        });

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->assertSame('image-bytes', $adapter->read('foo.jpg'));
    }

    #[Test]
    public function it_blocks_redirects_to_non_public_destinations()
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('get')->once()->andReturnUsing(function ($url, $options) {
            $options['allow_redirects']['on_redirect'](
                new Request('GET', 'https://example.com/foo.jpg'),
                new Response(302, ['Location' => 'http://169.254.169.254/latest/meta-data/']),
                new Uri('http://169.254.169.254/latest/meta-data/')
            );

            return new Response(200, [], 'should-not-return');
        });

        $adapter = new GuzzleAdapter('https://example.com', $client);

        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $adapter->read('foo.jpg');
    }
}
