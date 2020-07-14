<?php

namespace Tests\Composer;

use Exception;
use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Extend\AddonInstaller;
use Facades\Statamic\Marketplace\Marketplace;
use Tests\TestCase;

class AddonInstallerTest extends TestCase
{
    /** @test */
    public function it_can_install_addon()
    {
        Marketplace::shouldReceive('package')->with('addon/exists')->andReturn(['data' => ['product_id' => 1]]);
        Composer::shouldReceive('require')->with('addon/exists')->once();

        AddonInstaller::install('addon/exists');
    }

    /** @test */
    public function it_will_not_install_addons_that_dont_exist_on_the_marketplace()
    {
        Marketplace::shouldReceive('package')->with('addon/not-approved')->andReturnNull();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('addon/not-approved is not an installable package.');

        AddonInstaller::install('addon/not-approved');
    }

    /** @test */
    public function it_can_uninstall_addon()
    {
        Composer::shouldReceive('installed')->andReturn(collect([
            'addon/one' => [],
        ]));

        Composer::shouldReceive('remove')->with('addon/one')->once();

        AddonInstaller::uninstall('addon/one');
    }

    /** @test */
    public function it_can_only_uninstall_installed_addons()
    {
        Composer::shouldReceive('installed')->andReturn(collect([]));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('addon/not-installed is not installed.');

        AddonInstaller::uninstall('addon/not-installed');
    }
}
