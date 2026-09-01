<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\DeviceFlow;
use Statamic\Licensing\Outpost;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    #[Test]
    public function it_starts_the_device_flow_and_completes()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        Http::fake([
            DeviceFlow::START_URL => Http::response([
                'url' => 'https://statamic.com/licensing/device/AB12-CD34',
                'code' => 'AB12-CD34',
                'device_code' => 'dev_123',
                'interval' => 1,
            ]),
            DeviceFlow::POLL_URL => Http::response([
                'status' => 'complete',
            ]),
        ]);

        $this->mock(Outpost::class)->shouldReceive('clearCachedResponse')->once();

        $this->artisan('statamic:license', ['--poll-once' => true])
            ->expectsOutputToContain('https://statamic.com/licensing/device/AB12-CD34')
            ->expectsOutputToContain('AB12-CD34')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request->url() === DeviceFlow::START_URL
            && $request['key'] === 'site_abcdefghijklmnopqrstuvwxyz');
    }
}
