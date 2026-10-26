<?php

namespace Tests\Marketplace;

use Facades\GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Marketplace\Client;
use Tests\TestCase;

class ClientTest extends TestCase
{
    #[Test]
    public function it_sends_the_statamic_user_agent()
    {
        Guzzle::shouldReceive('request')
            ->once()
            ->withArgs(fn ($method, $endpoint, $options) => $options['headers']['user-agent'] === 'Statamic/3.0.0-testing')
            ->andReturn(new Response(200, [], '{}'));

        (new Client)->get('packages');
    }
}
