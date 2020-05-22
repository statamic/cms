<?php

namespace Tests\Licensing;

use Facades\Statamic\Licensing\Pro;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Addon;
use Statamic\Licensing\Outpost;
use Tests\TestCase;

class OutpostTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow();
    }

    /** @test */
    public function it_builds_the_request_payload()
    {
        config(['statamic.system.license_key' => 'test-key']);
        Pro::shouldReceive('check')->andReturnTrue();

        Addon::shouldReceive('all')->once()->andReturn(collect([
            new FakeOutpostAddon('foo/bar', '1.2.3'),
            new FakeOutpostAddon('baz/qux', '4.5.6'),
        ]));

        $this->assertEquals([
            'key' => 'test-key',
            'host' => 'localhost',
            'statamic_version' => '3.0.0-testing',
            'statamic_pro' => true,
            'packages' => [
                'foo/bar' => '1.2.3',
                'baz/qux' => '4.5.6',
            ],
        ], $this->outpost()->payload());
    }

    /** @test */
    public function it_contacts_the_outpost_and_caches_the_response()
    {
        $outpost = $this->outpostWithJsonResponse(['foo' => 'bar']);

        $expectedResponse = [
            'foo' => 'bar',
            'expiry' => now()->addHour()->timestamp,
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->with('statamic.outpost.response')->andReturnNull();
        Cache::shouldReceive('put')->once()->withArgs(function ($key, $value, $expiry) use ($expectedResponse) {
            return $key === 'statamic.outpost.response'
                && $value === $expectedResponse
                && $expiry->diffInMinutes() == 59;
        });

        $this->assertEquals($expectedResponse, $outpost->response());
    }

    /** @test */
    public function the_cached_response_is_used()
    {
        $outpost = $this->outpostWithJsonResponse(['newer' => 'response']);

        $testCachedResponse = [
            'cached' => 'response',
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->times(4)->with('statamic.outpost.response')->andReturn($testCachedResponse);
        Cache::shouldNotReceive('put');

        $first = $outpost->response();
        $second = $outpost->response();

        $this->assertEquals($testCachedResponse, $first);
        $this->assertSame($first, $second);
    }

    /** @test */
    public function the_cached_response_is_ignored_if_the_payload_is_different()
    {
        $outpost = $this->outpostWithJsonResponse(['newer' => 'response']);

        $expectedResponse = [
            'newer' => 'response',
            'expiry' => now()->addHour()->timestamp,
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->once()->with('statamic.outpost.response')->andReturn(['payload' => 'old']);
        Cache::shouldReceive('put')->once()->withArgs(function ($key, $value, $expiry) use ($expectedResponse) {
            return $key === 'statamic.outpost.response'
                && $value === $expectedResponse
                && $expiry->diffInMinutes() == 59;
        });

        $this->assertEquals($expectedResponse, $outpost->response());
    }

    /** @test */
    public function it_clears_the_cached_response()
    {
        Cache::shouldReceive('forget')->once()->with('statamic.outpost.response');

        $this->outpost()->clearCachedResponse();
    }

    /** @test */
    public function it_caches_a_timed_out_request_for_5_minutes_and_treats_it_like_a_500_error()
    {
        $outpost = $this->outpostWithResponse(
            new ConnectException('', new Request('POST', '/v3/query'))
        );

        $expectedResponse = [
            'error' => 500,
            'expiry' => now()->addMinutes(5)->timestamp,
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->with('statamic.outpost.response')->andReturnNull();
        Cache::shouldReceive('put')->once()->withArgs(function ($key, $value, $expiry) use ($expectedResponse) {
            return $key === 'statamic.outpost.response'
                && $value === $expectedResponse
                && $expiry->diffInMinutes() == 4;
        });

        $this->assertEquals($expectedResponse, $outpost->response());
    }

    /** @test */
    public function it_caches_a_500_error_for_5_minutes()
    {
        $outpost = $this->outpostWithErrorResponse(500);

        $expectedResponse = [
            'error' => 500,
            'expiry' => now()->addMinutes(5)->timestamp,
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->with('statamic.outpost.response')->andReturnNull();
        Cache::shouldReceive('put')->once()->withArgs(function ($key, $value, $expiry) use ($expectedResponse) {
            return $key === 'statamic.outpost.response'
                && $value === $expectedResponse
                && $expiry->diffInMinutes() == 4;
        });

        $this->assertEquals($expectedResponse, $outpost->response());
    }

    /** @test */
    public function it_caches_a_429_too_many_requests_error_for_the_length_described_in_the_retry_after_header()
    {
        $retryAfter = 23; // arbitrary number

        $outpost = $this->outpostWithErrorResponse(429, [
            'Retry-After' => [$retryAfter]
        ]);

        $expectedResponse = [
            'error' => 429,
            'expiry' => now()->addSeconds($retryAfter)->timestamp,
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->with('statamic.outpost.response')->andReturnNull();
        Cache::shouldReceive('put')->once()->withArgs(function ($key, $value, $expiry) use ($expectedResponse, $retryAfter) {
            return $key === 'statamic.outpost.response'
                && $value === $expectedResponse
                && $expiry->diffInSeconds() == $retryAfter-1;
        });

        $this->assertEquals($expectedResponse, $outpost->response());
    }

    /** @test */
    public function it_caches_a_422_validation_error_for_an_hour()
    {
        $outpost = $this->outpostWithErrorResponse(422, [], [
            'message' => 'The given data was invalid.',
            'errors' => [
                'a' => ['error one', 'error two'],
                'b' => ['error one'],
            ],
        ]);

        $expectedResponse = [
            'error' => 422,
            'errors' => [
                'a' => ['error one', 'error two'],
                'b' => ['error one'],
            ],
            'expiry' => now()->addHour()->timestamp,
            'payload' => $outpost->payload(),
        ];

        Cache::shouldReceive('get')->with('statamic.outpost.response')->andReturnNull();
        Cache::shouldReceive('put')->once()->withArgs(function ($key, $value, $expiry) use ($expectedResponse) {
            return $key === 'statamic.outpost.response'
                && $value === $expectedResponse
                && $expiry->diffInMinutes() == 59;
        });

        $this->assertEquals($expectedResponse, $outpost->response());
    }

    private function outpostWithJsonResponse(array $data)
    {
        return $this->outpostWithResponse(
            new Response(200, [], json_encode($data))
        );
    }

    private function outpostWithErrorResponse(int $status, array $headers = [], array $data = [])
    {
        $e = new RequestException(
            '',
            new Request('POST', '/v3/query'),
            new Response($status, $headers, json_encode($data))
        );

        return $this->outpostWithResponse($e);
    }

    private function outpostWithResponse($response)
    {
        $guzzle = new Client(['handler' => new MockHandler([$response])]);

        return $this->outpost($guzzle);
    }

    private function outpost($guzzle = null)
    {
        return new Outpost($guzzle ?? $this->mock(Client::class));
    }
}

class FakeOutpostAddon
{
    protected $package;
    protected $version;

    public function __construct($package, $version)
    {
        $this->package = $package;
        $this->version = $version;
    }

    public function package()
    {
        return $this->package;
    }

    public function version()
    {
        return $this->version;
    }
}
