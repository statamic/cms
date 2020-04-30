<?php

namespace Tests\Composer;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Updater\Updater;
use Statamic\Updater\Updater as RealUpdater;
use Tests\Fakes\Composer\Composer as FakeComposer;
use Tests\TestCase;

class UpdaterTest extends TestCase
{
    protected $shouldFakeVersion = false;

    public function setUp(): void
    {
        parent::setUp();

        Composer::swap(new FakeComposer);
        Composer::require('test/deaths-tar-vulnerability', '1.0.0');

        Updater::swap(RealUpdater::product('deaths-tar-vulnerability'));
        Updater::shouldReceive('getPackage')->andReturn('test/deaths-tar-vulnerability');
        Updater::shouldReceive('latestVersion')->andReturn('1.2.*');
        Updater::makePartial();
    }

    /** @test */
    public function it_can_update()
    {
        Updater::update();

        $this->assertEquals('1.0.1', Composer::installedVersion('test/deaths-tar-vulnerability'));
    }

    /** @test */
    public function it_can_downgrade_to_explicit_version()
    {
        Updater::update();

        $this->assertEquals('1.0.1', Composer::installedVersion('test/deaths-tar-vulnerability'));

        Updater::installExplicitVersion('1.0.0');

        $this->assertEquals('1.0.0', Composer::installedVersion('test/deaths-tar-vulnerability'));
    }

    /** @test */
    public function it_can_update_to_latest_version()
    {
        $this->assertEquals('1.0.0', Composer::installedVersion('test/deaths-tar-vulnerability'));

        Updater::updateToLatest();

        $this->assertNotEquals('1.0.0', Composer::installedVersion('test/deaths-tar-vulnerability'));
        $this->assertEquals('1.2.9', Composer::installedVersion('test/deaths-tar-vulnerability'));
    }
}
