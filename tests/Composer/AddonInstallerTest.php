<?php

namespace Tests\Composer;

use Exception;
use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Extend\AddonInstaller;
use Facades\Statamic\Extend\Marketplace;
use Tests\Fakes\Composer\Composer as FakeComposer;
use Tests\Fakes\Composer\Marketplace as FakeMarketplace;
use Tests\TestCase;

class AddonInstallerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Marketplace::swap(new FakeMarketplace);
        Composer::swap(new FakeComposer);
    }

    /** @test */
    public function there_are_installable_addons_by_default()
    {
        $this->assertCount(3, AddonInstaller::installable());
    }

    /** @test */
    public function there_are_no_installed_addons_by_default()
    {
        $this->assertCount(0, AddonInstaller::installed());
    }

    /** @test */
    public function it_can_install_addon()
    {
        AddonInstaller::install('addon/one');

        $this->assertCount(1, AddonInstaller::installed());
        $this->assertContains('addon/one', AddonInstaller::installed());
    }

    /** @test */
    public function it_can_uninstall_addon()
    {
        AddonInstaller::install('addon/one');

        $this->assertCount(1, AddonInstaller::installed());

        AddonInstaller::uninstall('addon/one');

        $this->assertCount(0, AddonInstaller::installed());
    }

    /** @test */
    public function it_will_not_install_unapproved_addon()
    {
        $this->expectException(Exception::class);

        AddonInstaller::install('addon/not-approved');

        $this->assertTrue($exception instanceof \Exception);
        $this->assertCount(0, AddonInstaller::installed());
    }

    /** @test */
    public function it_will_not_uninstall_unapproved_addon()
    {
        AddonInstaller::install('addon/one');

        $this->assertCount(1, AddonInstaller::installed());

        $this->expectException(Exception::class);

        AddonInstaller::uninstall('addon/not-approved');

        $this->assertCount(1, AddonInstaller::installed());
    }
}
