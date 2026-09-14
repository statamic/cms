<?php

namespace Tests\Auth\Protect;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Protect\ProtectorManager;

class IpProtectorTest extends PageProtectionTestCase
{
    #[Test]
    public function allows_matching_ip()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);

        $this
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '123.4.5.6'])
            ->assertOk();
    }

    #[Test]
    public function denies_for_incorrect_ip()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);

        $this
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '786.54.32.1'])
            ->assertStatus(403);
    }

    #[Test]
    public function denies_when_no_ip_addresses_are_configured()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => [],
        ]]);

        $this
            ->requestPageProtectedBy('ip_address')
            ->assertStatus(403);
    }

    #[Test]
    public function allows_when_client_ip_in_forwarded_chain()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => '*']);

        // Simulate request behind a load balancer (e.g. Google Cloud Platform): REMOTE_ADDR is proxy, client IP in X-Forwarded-For.
        $this
            ->withHeader('X-Forwarded-For', '123.4.5.6')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertOk();
    }

    #[Test]
    public function denies_when_no_ip_in_forwarded_chain_matches()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => '*']);

        $this
            ->withHeader('X-Forwarded-For', '10.0.0.2')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertStatus(403);
    }

    /**
     * Regression test: when ip() returns the proxy and ips() contains the client (e.g. Google Cloud Platform),
     * only the ips()-based protector allows access.
     */
    #[Test]
    public function allows_when_only_ips_chain_contains_allowed_ip_not_ip()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);

        $original = RequestFacade::getFacadeRoot();
        $request = Request::create('/test');
        $request = \Mockery::mock($request)->makePartial();
        $request->shouldReceive('ip')->andReturn('10.0.0.1');
        $request->shouldReceive('ips')->andReturn(['10.0.0.1', '123.4.5.6']);

        RequestFacade::swap($request);

        try {
            $protector = $this->app->make(ProtectorManager::class)->driver('ip_address');
            $protector->protect();
            $this->addToAssertionCount(1);
        } finally {
            RequestFacade::swap($original);
        }
    }
}
