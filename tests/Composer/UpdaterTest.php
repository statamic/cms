<?php

namespace Tests\Composer;

use Tests\TestCase;
use Statamic\Statamic;
use Facades\Statamic\Updater\Updater;
use Facades\Statamic\Updater\Changelog;
use Statamic\Updater\Updater as RealUpdater;
use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\Composer\Composer as FakeComposer;
use Tests\Fakes\Composer\Changelog as FakeChangelog;

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
    function it_can_update()
    {
        Updater::update();

        $this->assertEquals('1.0.1', Composer::installedVersion('test/deaths-tar-vulnerability'));
    }

    /** @test */
    function it_can_downgrade_to_explicit_version()
    {
        Updater::update();

        $this->assertEquals('1.0.1', Composer::installedVersion('test/deaths-tar-vulnerability'));

        Updater::installExplicitVersion('1.0.0');

        $this->assertEquals('1.0.0', Composer::installedVersion('test/deaths-tar-vulnerability'));
    }

    /** @test */
    function it_can_update_to_latest_version()
    {
        $this->assertEquals('1.0.0', Composer::installedVersion('test/deaths-tar-vulnerability'));

        Updater::updateToLatest();

        $this->assertNotEquals('1.0.0', Composer::installedVersion('test/deaths-tar-vulnerability'));
        $this->assertEquals('1.2.9', Composer::installedVersion('test/deaths-tar-vulnerability'));
    }
}
