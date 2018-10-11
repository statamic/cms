<?php

namespace Tests\Composer;

use Facades\Statamic\Extend\AddonInstaller;
use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Composer\Marketplace;
use Tests\Fakes\Composer\Composer as FakeComposer;
use Tests\Fakes\Composer\Marketplace as FakeMarketplace;
use Tests\TestCase;

class AddonInstallerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Marketplace::swap(new FakeMarketplace);
        Composer::swap(new FakeComposer);
    }

    /** @test */
    function there_are_installable_addons_by_default()
    {
        $this->assertCount(3, AddonInstaller::installable());
    }

    /** @test */
    function there_are_no_installed_addons_by_default()
    {
        $this->assertCount(0, AddonInstaller::installed());
    }

    /** @test */
    function it_can_install_addon()
    {
        AddonInstaller::install('addon/one');

        $this->assertCount(1, AddonInstaller::installed());
        $this->assertContains('addon/one', AddonInstaller::installed());
    }

    /** @test */
    function it_can_uninstall_addon()
    {
        AddonInstaller::install('addon/one');

        $this->assertCount(1, AddonInstaller::installed());

        AddonInstaller::uninstall('addon/one');

        $this->assertCount(0, AddonInstaller::installed());
    }

    /** @test */
    function it_will_not_install_unapproved_addon()
    {
        try {
            AddonInstaller::install('addon/not-approved');
        } catch (\Exception $exception) {
            //
        }

        $this->assertTrue($exception instanceof \Exception);
        $this->assertCount(0, AddonInstaller::installed());
    }

    /** @test */
    function it_will_not_uninstall_unapproved_addon()
    {
        AddonInstaller::install('addon/one');

        $this->assertCount(1, AddonInstaller::installed());

        try {
            AddonInstaller::uninstall('addon/not-approved');
        } catch (\Exception $exception) {
            //
        }

        $this->assertTrue($exception instanceof \Exception);
        $this->assertCount(1, AddonInstaller::installed());
    }
}
