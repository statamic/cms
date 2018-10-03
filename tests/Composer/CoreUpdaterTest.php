<?php

namespace Tests\Composer;

use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\CoreChangelog;
use Facades\Statamic\Composer\CoreUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\Composer\Composer as FakeComposer;
use Tests\Fakes\Composer\CoreChangelog as FakeCoreChangelog;
use Tests\TestCase;

class CoreUpdaterTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        CoreChangelog::swap(new FakeCoreChangelog);
        Composer::swap(new FakeComposer);
        Composer::require('statamic/cms');
    }

    /** @test */
    function it_can_get_current_core_version()
    {
        $this->assertEquals('1.0.0', CoreUpdater::currentVersion());
    }

    /** @test */
    function it_can_update_core()
    {
        CoreUpdater::update();

        $this->assertEquals('1.0.1', CoreUpdater::currentVersion());
    }

    /** @test */
    function it_can_downgrade_core_to_explicit_version()
    {
        CoreUpdater::update();

        $this->assertEquals('1.0.1', CoreUpdater::currentVersion());

        CoreUpdater::installExplicitVersion('1.0.0');

        $this->assertEquals('1.0.0', CoreUpdater::currentVersion());
    }

    /** @test */
    function it_can_update_to_latest_version()
    {
        $this->assertEquals('1.0.0', CoreUpdater::currentVersion());

        CoreUpdater::updateToLatest();

        $this->assertNotEquals('1.0.0', CoreUpdater::currentVersion());
        $this->assertEquals(CoreUpdater::latestVersion(), CoreUpdater::currentVersion());
    }
}
