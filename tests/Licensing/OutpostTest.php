<?php

namespace Tests\Licensing;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_builds_the_request_payload()
    {
        config(['statamic.system.license_key' => 'test-key']);
        config(['statamic.editions.pro' => true]);

        Addon::shouldReceive('all')->once()->andReturn(collect([
            new FakeOutpostAddon('foo/bar', '1.2.3', null, true, true),
            new FakeOutpostAddon('baz/qux', '4.5.6', 'example', true, true),
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
            'laravel_version' => app()->version(),
            'packages' => [
                'foo/bar' => ['version' => '1.2.3', 'edition' => null],
                'baz/qux' => ['version' => '4.5.6', 'edition' => 'example'],
            ],
        ], $this->outpost()->payload());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function license_key_file_is_used_when_it_exists()
    {
        config(['statamic.system.license_key' => 'testsitekey12345']);

        $encrypter = new Encrypter('testsitekey12345');
        $encryptedKeyFile = $encrypter->encrypt(json_encode([
            'foo' => 'bar',
            'packages' => [],
        ]));

        File::shouldReceive('exists')
            ->with(storage_path('license.key'))
            ->once()
            ->andReturnTrue();

        File::shouldReceive('get')
            ->with(storage_path('license.key'))
            ->once()
            ->andReturn($encryptedKeyFile);

        $outpost = $this->outpostWithJsonResponse(['newer' => 'response']);
        $response = $outpost->response();

        $this->assertArraySubset([
            'foo' => 'bar',
            'packages' => [],
        ], $response);
    }

    #[Test]
    public function license_key_file_response_merges_installed_addons_into_response()
    {
        config(['statamic.system.license_key' => 'testsitekey12345']);

        $encrypter = new Encrypter('testsitekey12345');
        $encryptedKeyFile = $encrypter->encrypt(json_encode(['packages' => [
            'foo/bar' => ['valid' => true, 'exists' => true, 'version_limit' => null],
        ]]));

        File::shouldReceive('exists')
            ->with(storage_path('license.key'))
            ->once()
            ->andReturnTrue();

        File::shouldReceive('get')
            ->with(storage_path('license.key'))
            ->once()
            ->andReturn($encryptedKeyFile);

        Addon::shouldReceive('all')->andReturn(collect([
            (new FakeOutpostAddon('foo/bar', '1.2.3', null, true, true)),
            (new FakeOutpostAddon('bar/baz', '1.2.3', null, true, true)),
            (new FakeOutpostAddon('private/addon', '1.2.3', null, false, false)),
        ]));

        $outpost = $this->outpostWithJsonResponse(['newer' => 'response']);
        $response = $outpost->response();

        $this->assertArraySubset([
            'packages' => [
                'foo/bar' => ['valid' => true, 'exists' => true, 'version_limit' => null],
                'bar/baz' => ['valid' => false, 'exists' => true, 'version_limit' => null],
                'private/addon' => ['valid' => true, 'exists' => false, 'version_limit' => null],
            ],
        ], $response);
    }

    #[Test]
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

    #[Test]
    public function it_clears_the_cached_response()
    {
        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('forget')->once()->with('statamic.outpost.response')->getMock()
        );

        $this->outpost()->clearCachedResponse();
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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
    protected $existsOnMarketplace;
    protected $isCommercial;

    public function __construct($package, $version, $edition, $existsOnMarketplace, $isCommercial)
    {
        $this->package = $package;
        $this->version = $version;
        $this->edition = $edition;
        $this->existsOnMarketplace = $existsOnMarketplace;
        $this->isCommercial = $isCommercial;
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

    public function existsOnMarketplace()
    {
        return $this->existsOnMarketplace;
    }

    public function isCommercial()
    {
        return $this->isCommercial;
    }
}
