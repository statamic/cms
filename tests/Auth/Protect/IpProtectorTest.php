<?php

namespace Tests\Auth\Protect;

use PHPUnit\Framework\Attributes\Test;

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
    public function allows_client_ip_from_behind_a_trusted_proxy()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => ['10.0.0.1']]);

        $this
            ->withHeader('X-Forwarded-For', '123.4.5.6')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertOk();
    }

    #[Test]
    public function denies_client_ip_from_behind_a_trusted_proxy()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => ['10.0.0.1']]);

        $this
            ->withHeader('X-Forwarded-For', '10.0.0.2')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertStatus(403);
    }

    #[Test]
    public function denies_an_allowed_ip_spoofed_earlier_in_the_forwarded_chain()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => ['10.0.0.1']]);

        // Only the entry nearest the trusted proxy is the resolved client. Anything before it
        // was written by the client, so an allowed address placed there must not be honored.
        $this
            ->withHeader('X-Forwarded-For', '123.4.5.6, 203.0.113.9')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertStatus(403);
    }

    #[Test]
    public function allows_client_ip_when_a_load_balancer_appends_its_own_trusted_address()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => ['130.211.0.0/22', '34.120.0.1']]);

        // Google Cloud's load balancer appends its forwarding rule address to the chain.
        $this
            ->withHeader('X-Forwarded-For', '123.4.5.6, 34.120.0.1')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '130.211.0.5'])
            ->assertOk();
    }

    #[Test]
    public function denies_a_spoofed_chain_when_a_load_balancer_appends_its_own_trusted_address()
    {
        config(['statamic.protect.schemes.ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['123.4.5.6'],
        ]]);
        config(['trustedproxy.proxies' => ['130.211.0.0/22', '34.120.0.1']]);

        $this
            ->withHeader('X-Forwarded-For', '123.4.5.6, 203.0.113.9, 34.120.0.1')
            ->requestPageProtectedBy('ip_address', ['REMOTE_ADDR' => '130.211.0.5'])
            ->assertStatus(403);
    }
}
