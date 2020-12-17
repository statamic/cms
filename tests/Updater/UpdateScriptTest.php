<?php

namespace Tests\Updater;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\UpdateScripts\UpdateScript;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class UpdateScriptTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->lockPath = base_path('composer.lock');
        $this->previousLockPath = storage_path('statamic/updater/composer.lock.bak');

        $this->removeLockFiles();
    }

    public function tearDown(): void
    {
        $this->removeLockFiles();

        parent::tearDown();
    }

    /** @test */
    public function it_errors_when_no_lock_file_is_detected()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->previousLockPath);

        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage("Could not find a composer lock file at [{$this->lockPath}].");

        new UpdatePermissions;
    }

    /** @test */
    public function it_errors_when_no_previous_lock_file_is_present()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->lockPath);

        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage("Could not find a composer lock file at [{$this->previousLockPath}].");

        new UpdatePermissions;
    }

    /** @test */
    public function it_can_register_itself_with_statamic()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $registered = app('statamic.update-scripts');

        $this->assertInstanceOf(Collection::class, $registered);
        $this->assertNotContains(UpdatePermissions::class, $registered);
        $this->assertNotContains(UpdateSeoProDefaultContent::class, $registered);

        $initialCount = $registered->count();

        UpdatePermissions::register();
        UpdateSeoProDefaultContent::register();

        $this->assertInstanceOf(Collection::class, $registered);
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateSeoProDefaultContent::class, $registered);
        $this->assertCount($initialCount + 2, $registered);
    }

    /** @test */
    public function it_can_check_if_package_is_updating_to_specific_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $script = new UpdatePermissions;

        $this->assertTrue($script->isUpdatingTo('3.1.8'));
        $this->assertFalse($script->isUpdatingTo('3.0.25'));
        $this->assertFalse($script->isUpdatingTo('2.0'));
    }

    /** @test */
    public function it_can_check_if_package_is_updating_past_a_specific_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $script = new UpdatePermissions;

        $this->assertTrue($script->isUpdatingTo('3.1.9'));
        $this->assertTrue($script->isUpdatingTo('3.2'));
        $this->assertTrue($script->isUpdatingTo('4.0'));
        $this->assertFalse($script->isUpdatingTo('3.0.25'));
        $this->assertFalse($script->isUpdatingTo('2.0'));
    }

    private function removeLockFiles()
    {
        foreach ([$this->lockPath, $this->previousLockPath] as $lockFile) {
            if ($this->files->exists($lockFile)) {
                $this->files->delete($lockFile);
            }
        }
    }
}

class UpdatePermissions extends UpdateScript
{
    public function package()
    {
        return 'statamic/cms';
    }

    public function shouldUpdate($newVersion, $oldVersion)
    {
        //
    }

    public function update()
    {
        //
    }
}

class UpdateSeoProDefaultContent extends UpdateScript
{
    public function package()
    {
        return 'statamic/seo-pro';
    }

    public function shouldUpdate($newVersion, $oldVersion)
    {
        //
    }

    public function update()
    {
        //
    }
}
