<?php

namespace Tests\Updater;

use Facades\Statamic\Marketplace\Marketplace;
use Facades\Statamic\Version;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Addon;
use Statamic\Updater\UpdatesOverview;
use Tests\TestCase;

class UpdatesOverviewTest extends TestCase
{
    protected $shouldFakeVersion = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearUpdatesOverviewCache();
    }

    protected function clearUpdatesOverviewCache(): void
    {
        Cache::forget('updates-overview.count');
        Cache::forget('updates-overview.statamic');
        Cache::forget('updates-overview.security');
        Cache::forget('updates-overview.addons');
    }

    protected function setDefaultVersion(): void
    {
        Version::shouldReceive('get')->andReturn('3.0.0-testing');
    }

    #[Test]
    public function it_shows_statamic_update()
    {
        $this->setDefaultVersion();
        $this->mockMarketplaceStatamicChangelog('upgrade');
        Addon::shouldReceive('all')->andReturn(collect());

        $overview = new UpdatesOverview;

        $this->assertSame(1, $overview->count());
        $this->assertSame(['count' => 1, 'security' => false], $overview->badge());
        $this->assertTrue($overview->hasStatamicUpdate());
        $this->assertSame([], $overview->updatableAddons());
    }

    #[Test]
    public function it_shows_security_statamic_update()
    {
        $this->setDefaultVersion();
        $this->mockMarketplaceStatamicChangelog('upgrade', hasSecurityUpdate: true);
        Addon::shouldReceive('all')->andReturn(collect());

        $overview = new UpdatesOverview;

        $this->assertSame(1, $overview->count());
        $this->assertSame(['count' => 1, 'security' => true], $overview->badge());
        $this->assertTrue($overview->hasStatamicUpdate());
        $this->assertSame([], $overview->updatableAddons());
    }

    #[Test]
    public function it_shows_no_statamic_update()
    {
        $this->setDefaultVersion();
        $this->mockMarketplaceStatamicChangelog('current');
        Addon::shouldReceive('all')->andReturn(collect());

        $overview = new UpdatesOverview;

        $this->assertSame(0, $overview->count());
        $this->assertFalse($overview->hasStatamicUpdate());
        $this->assertSame([], $overview->updatableAddons());
    }

    #[Test]
    #[DataProvider('devVersionProvider')]
    public function it_does_not_increment_count_or_offer_statamic_update_for_dev_versions(string $version)
    {
        Version::shouldReceive('get')->andReturn($version);
        $this->mockMarketplaceStatamicChangelog('upgrade');
        Addon::shouldReceive('all')->andReturn(collect());

        $overview = new UpdatesOverview;

        $this->assertSame(0, $overview->count());
        $this->assertFalse($overview->hasStatamicUpdate());
        $this->assertSame([], $overview->updatableAddons());
    }

    public static function devVersionProvider(): array
    {
        return [
            'version starts with dev' => ['dev-main'],
            'version ends with dev' => ['3.0.0-dev'],
        ];
    }

    #[Test]
    public function it_shows_addon_updates()
    {
        $this->setDefaultVersion();
        $this->mockMarketplaceStatamicChangelog('current');
        $addon1 = $this->mockAddon('vendor/one', isLatestVersion: false, hasSecurityUpdate: false);
        $addon2 = $this->mockAddon('vendor/two', isLatestVersion: true, hasSecurityUpdate: false);
        $addon3 = $this->mockAddon('vendor/three', isLatestVersion: false, hasSecurityUpdate: false);
        Addon::shouldReceive('all')->andReturn(collect([$addon1, $addon2, $addon3]));

        $overview = new UpdatesOverview;

        $this->assertSame(2, $overview->count());
        $this->assertSame(['count' => 2, 'security' => false], $overview->badge());
        $this->assertFalse($overview->hasStatamicUpdate());
        $this->assertSame(['vendor/one', 'vendor/three'], $overview->updatableAddons());
    }

    #[Test]
    public function it_shows_security_addon_updates()
    {
        $this->setDefaultVersion();
        $this->mockMarketplaceStatamicChangelog('current');
        $addon1 = $this->mockAddon('vendor/one', isLatestVersion: false, hasSecurityUpdate: true);
        $addon2 = $this->mockAddon('vendor/two', isLatestVersion: true, hasSecurityUpdate: false);
        $addon3 = $this->mockAddon('vendor/three', isLatestVersion: false, hasSecurityUpdate: false);
        Addon::shouldReceive('all')->andReturn(collect([$addon1, $addon2, $addon3]));

        $overview = new UpdatesOverview;

        $this->assertSame(2, $overview->count());
        $this->assertSame(['count' => 2, 'security' => true], $overview->badge());
        $this->assertFalse($overview->hasStatamicUpdate());
        $this->assertSame(['vendor/one', 'vendor/three'], $overview->updatableAddons());
    }

    #[Test]
    public function it_returns_cached_values_without_calling_marketplace_or_addons()
    {
        $cachedCount = 2;
        $cachedStatamic = true;
        $cachedSecurity = true;
        $cachedAddons = ['vendor/one', 'vendor/two'];

        Cache::forever('updates-overview.count', $cachedCount);
        Cache::forever('updates-overview.statamic', $cachedStatamic);
        Cache::forever('updates-overview.security', $cachedSecurity);
        Cache::forever('updates-overview.addons', $cachedAddons);

        Marketplace::shouldReceive('statamic')->never();
        Addon::shouldReceive('all')->never();

        $overview = new UpdatesOverview;

        $this->assertSame($cachedCount, $overview->count());
        $this->assertSame($cachedStatamic, $overview->hasStatamicUpdate());
        $this->assertSame($cachedSecurity, $overview->hasSecurityUpdate());
        $this->assertSame($cachedAddons, $overview->updatableAddons());
        $this->assertSame(['count' => $cachedCount, 'security' => $cachedSecurity], $overview->badge());
    }

    protected function mockMarketplaceStatamicChangelog(string $latestType, bool $hasSecurityUpdate = false): void
    {
        $latest = (object) ['type' => $latestType];
        $changelog = \Mockery::mock();
        $changelog->shouldReceive('latest')->andReturn($latest);
        $changelog->shouldReceive('hasSecurityUpdate')->andReturn($hasSecurityUpdate);
        $statamic = \Mockery::mock();
        $statamic->shouldReceive('changelog')->andReturn($changelog);
        Marketplace::shouldReceive('statamic')->andReturn($statamic);
    }

    protected function mockAddon(string $id, bool $isLatestVersion, bool $hasSecurityUpdate = false)
    {
        return new class($id, $isLatestVersion, $hasSecurityUpdate)
        {
            public function __construct(
                private readonly string $addonId,
                private readonly bool $isLatest,
                private readonly bool $hasSecurityUpdate
            ) {
            }

            public function id(): string
            {
                return $this->addonId;
            }

            public function isLatestVersion(): bool
            {
                return $this->isLatest;
            }

            public function changelog()
            {
                $changelog = \Mockery::mock();
                $changelog->shouldReceive('hasSecurityUpdate')->andReturn($this->hasSecurityUpdate);

                return $changelog;
            }
        };
    }
}
