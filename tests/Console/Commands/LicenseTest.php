<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\DeviceFlow;
use Statamic\Licensing\LicenseManager;
use Statamic\Licensing\SiteLicense;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    #[Test]
    public function it_starts_the_device_flow_when_the_site_is_not_connected()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->mockLicenses('connect');

        Http::fake([
            DeviceFlow::START_URL => Http::response([
                'url' => 'https://statamic.com/account/licensing/device/AB12-CD34',
                'code' => 'AB12-CD34',
                'device_code' => 'dev_123',
                'interval' => 1,
            ]),
            DeviceFlow::POLL_URL => Http::response([
                'status' => 'complete',
            ]),
        ]);

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('https://statamic.com/account/licensing/device/AB12-CD34')
            ->expectsOutputToContain('AB12-CD34')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request->url() === DeviceFlow::START_URL
            && $request['key'] === 'site_abcdefghijklmnopqrstuvwxyz'
            && $request['name'] === config('app.name'));
    }

    #[Test]
    public function it_skips_the_device_flow_when_already_licensed()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->mockLicenses(null);

        Http::fake();

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('already licensed')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    #[Test]
    public function it_skips_the_device_flow_when_already_connected()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->mockLicenses('buy');

        Http::fake();

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('already connected')
            ->expectsOutputToContain('https://statamic.com/account/sites/site_abcdefghijklmnopqrstuvwxyz')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    #[Test]
    public function it_skips_the_device_flow_when_the_domain_is_invalid()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->mockLicenses('domain');

        Http::fake();

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('this domain is not on the site record')
            ->expectsOutputToContain('https://statamic.com/account/sites/site_abcdefghijklmnopqrstuvwxyz')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    #[Test]
    public function it_skips_the_device_flow_when_using_a_license_key_file()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->mockLicenses('connect', usingLicenseKeyFile: true);

        Http::fake();

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('offline license key file')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    #[Test]
    public function it_starts_the_device_flow_when_outpost_cannot_be_reached()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->mockLicenses('connect', requestFailed: true);

        Http::fake([
            DeviceFlow::START_URL => Http::response([
                'url' => 'https://statamic.com/account/licensing/device/AB12-CD34',
                'code' => 'AB12-CD34',
                'device_code' => 'dev_123',
                'interval' => 1,
            ]),
            DeviceFlow::POLL_URL => Http::response([
                'status' => 'complete',
            ]),
        ]);

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('AB12-CD34')
            ->assertSuccessful();
    }

    private function mockLicenses(?string $action, bool $usingLicenseKeyFile = false, bool $requestFailed = false): void
    {
        $licenses = $this->mock(LicenseManager::class);
        $licenses->shouldReceive('refresh');
        $licenses->shouldReceive('usingLicenseKeyFile')->andReturn($usingLicenseKeyFile);
        $licenses->shouldReceive('requestFailed')->andReturn($requestFailed);
        $licenses->shouldReceive('primaryAction')->andReturn($action);
        $licenses->shouldReceive('site')->andReturn(new SiteLicense([
            'key' => 'site_abcdefghijklmnopqrstuvwxyz',
        ]));
    }
}
