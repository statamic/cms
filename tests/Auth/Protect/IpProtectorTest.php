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
}
