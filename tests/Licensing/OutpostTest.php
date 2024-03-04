<?php

namespace Tests\Licensing;

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
        Carbon::setTestNow(now()->startOfMinute());
        Cache::store('outpost')->flush();
    }

    /** @test */
    public function it_builds_the_request_payload()
    {
        config(['statamic.system.license_key' => 'test-key']);
        config(['statamic.editions.pro' => true]);

        Addon::shouldReceive('all')->once()->andReturn(collect([
            new FakeOutpostAddon('foo/bar', '1.2.3', null),
            new FakeOutpostAddon('baz/qux', '4.5.6', 'example'),
        ]));

        request()->server->set('SERVER_ADDR', '123.123.123.123');
        request()->server->set('SERVER_PORT', 123);

        $this->assertEquals([
            'key' => 'test-key',
            'host' => 'localhost',
            'ip' => '123.123.123.123',
            'port' => 123,
            'statamic_version' => '3.0.0-testing',
            'statamic_pro' => true,
            'php_version' => PHP_VERSION,
            'packages' => [
                'foo/bar' => ['version' => '1.2.3', 'edition' => null],
                'baz/qux' => ['version' => '4.5.6', 'edition' => 'example'],
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

        $this->assertEquals($expectedResponse, $outpost->response());
        Carbon::setTestNow(now()->addHour()->subSecond(1));
        $this->assertCachedResponseEquals($expectedResponse);
        Carbon::setTestNow(now()->addSeconds(1));
        $this->assertResponseNotCached();
    }

    /** @test */
    public function the_cached_response_is_used()
    {
        $outpost = $this->outpostWithJsonResponse(['newer' => 'response']);

        $this->setCachedResponse($testCachedResponse = [
            'cached' => 'response',
            'payload' => $outpost->payload(),
        ]);

        $first = $outpost->response();
        $second = $outpost->response();

        $this->assertEquals($testCachedResponse, $first);
        $this->assertSame($first, $second);
    }

    /** @test */
    public function the_cached_response_is_ignored_if_the_payload_is_different()
    {
        $this->setCachedResponse(['payload' => ['old' => 'stuff']]);
        $outpost = $this->outpostWithJsonResponse(['newer' => 'response']);

        $expectedResponse = [
            'newer' => 'response',
            'expiry' => now()->addHour()->timestamp,
            'payload' => $outpost->payload(),
        ];

        $this->assertEquals($expectedResponse, $outpost->response());
        Carbon::setTestNow(now()->addHour()->subSecond(1));
        $this->assertCachedResponseEquals($expectedResponse);
        Carbon::setTestNow(now()->addSeconds(1));
        $this->assertResponseNotCached();
    }

    /** @test */
    public function it_clears_the_cached_response()
    {
        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('forget')->once()->with('statamic.outpost.response')->getMock()
        );

        $this->outpost()->clearCachedResponse();
    }

    /** @test */
    public function it_caches_a_timed_out_request_for_5_minutes()
    {
        $outpost = $this->outpostWithResponse(
            $e = new ConnectException('', new Request('POST', '/v3/query'))
        );

        $expectedResponse = [
            'error' => $e->getCode(),
            'expiry' => now()->addMinutes(5)->timestamp,
            'payload' => $outpost->payload(),
        ];

        $this->assertEquals($expectedResponse, $outpost->response());
        Carbon::setTestNow(now()->addMinutes(5)->subSecond(1));
        $this->assertCachedResponseEquals($expectedResponse);
        Carbon::setTestNow(now()->addSeconds(1));
        $this->assertResponseNotCached();
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

        $this->assertEquals($expectedResponse, $outpost->response());
        Carbon::setTestNow(now()->addMinutes(5)->subSecond(1));
        $this->assertCachedResponseEquals($expectedResponse);
        Carbon::setTestNow(now()->addSeconds(1));
        $this->assertResponseNotCached();
    }

    /** @test */
    public function it_caches_a_429_too_many_requests_error_for_the_length_described_in_the_retry_after_header()
    {
        $retryAfter = 23; // arbitrary number

        $outpost = $this->outpostWithErrorResponse(429, [
            'Retry-After' => [$retryAfter],
        ]);

        $expectedResponse = [
            'error' => 429,
            'expiry' => now()->addSeconds($retryAfter)->timestamp,
            'payload' => $outpost->payload(),
        ];

        $this->assertEquals($expectedResponse, $outpost->response());
        Carbon::setTestNow(now()->addSeconds($retryAfter - 1));
        $this->assertCachedResponseEquals($expectedResponse);
        Carbon::setTestNow(now()->addSeconds(1));
        $this->assertResponseNotCached();
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

        $this->assertEquals($expectedResponse, $outpost->response());
        Carbon::setTestNow(now()->addHour()->subSecond(1));
        $this->assertCachedResponseEquals($expectedResponse);
        Carbon::setTestNow(now()->addSeconds(1));
        $this->assertResponseNotCached();
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

    private function assertCachedResponseEquals($expected)
    {
        return $this->assertEquals($expected, Cache::store('outpost')->get('statamic.outpost.response'));
    }

    private function assertResponseNotCached()
    {
        $this->assertNull(Cache::store('outpost')->get('statamic.outpost.response'));
    }

    private function setCachedResponse($response)
    {
        Cache::store('outpost')->put('statamic.outpost.response', $response);
    }
}

class FakeOutpostAddon
{
    protected $package;
    protected $version;
    protected $edition;

    public function __construct($package, $version, $edition)
    {
        $this->package = $package;
        $this->version = $version;
        $this->edition = $edition;
    }

    public function package()
    {
        return $this->package;
    }

    public function version()
    {
        return $this->version;
    }

    public function edition()
    {
        return $this->edition;
    }
}
