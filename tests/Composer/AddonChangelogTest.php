<?php

namespace Tests\Composer;

use Facades\GuzzleHttp\Client;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Addons\Addon;
use Statamic\Updater\AddonChangelog;
use Tests\TestCase;

class AddonChangelogTest extends TestCase
{
    use ChangelogTests;

    protected function changelog()
    {
        $addon = Mockery::mock(new Addon('test'));
        $addon->shouldReceive('version')->andReturn('1.0.1');
        $addon->shouldReceive('license->versionLimit')->atLeast()->once()->andReturn('2.0.0');
        $addon->shouldReceive('package')->atLeast()->once();

        return new AddonChangelog($addon);
    }

    #[Test]
    public function it_checks_for_licensing_limits()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse(['2.0.0', '1.0.3', '1.0.2', '1.0.1', '1.0.0']));

        $changelog = $this->changelog();

        $contents = $changelog->get();

        $this->assertCount(5, $contents);
        $this->assertEquals([false, true, true, true, true], $contents->map->licensed->all());
    }

    #[Test]
    public function release_is_always_licensed_if_theres_no_license_limit()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse(['2.0.0', '1.0.3', '1.0.2', '1.0.1', '1.0.0']));

        $addon = Mockery::mock(new Addon('test'));
        $addon->shouldReceive('version')->andReturn('1.0.1');
        $addon->shouldReceive('license->versionLimit')->atLeast()->once()->andReturnNull();
        $addon->shouldReceive('package')->atLeast()->once();
        $changelog = new AddonChangelog($addon);

        $contents = $changelog->get();

        $this->assertCount(5, $contents);
        $this->assertEquals([true, true, true, true, true], $contents->map->licensed->all());
    }
}
