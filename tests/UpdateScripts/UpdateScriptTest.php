<?php

namespace Tests\UpdateScripts;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Auth\UserCollection;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Path;
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
    public function it_errors_when_instantiating_with_no_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->previousLockPath);

        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage('Could not find a composer lock file at ['.Path::makeRelative($this->lockPath).'].');

        new UpdatePermissions;
    }

    /** @test */
    public function it_errors_when_instantiating_with_no_previous_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->lockPath);

        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage('Could not find a composer lock file at ['.Path::makeRelative($this->previousLockPath).'].');

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
        $this->assertNotContains(UpdateTrees::class, $registered);

        $initialCount = $registered->count();

        UpdatePermissions::register();
        UpdateTrees::register();

        $this->assertInstanceOf(Collection::class, $registered);
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertCount($initialCount + 2, $registered);
    }

    /** @test */
    public function it_silently_fails_to_register_on_older_statamic_versions()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        app()->forgetInstance('statamic.update-scripts');

        $this->assertNull(UpdatePermissions::register());
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

        $this->assertFalse($script->isUpdatingTo('4.0.0'));
        $this->assertFalse($script->isUpdatingTo('4.0'));
        $this->assertFalse($script->isUpdatingTo('3.2.0'));
        $this->assertFalse($script->isUpdatingTo('3.2'));
        $this->assertFalse($script->isUpdatingTo('3.1.9'));
        $this->assertTrue($script->isUpdatingTo('3.1.8'));
        $this->assertTrue($script->isUpdatingTo('3.1.7'));
        $this->assertTrue($script->isUpdatingTo('3.1.0'));
        $this->assertTrue($script->isUpdatingTo('3.1'));
        $this->assertTrue($script->isUpdatingTo('3.0.26'));
        $this->assertFalse($script->isUpdatingTo('3.0.25'));
        $this->assertFalse($script->isUpdatingTo('3.0.24'));
        $this->assertFalse($script->isUpdatingTo('3.0.0-beta.1'));
        $this->assertTrue($script->isUpdatingTo('3.1.0-beta.1'));
        $this->assertFalse($script->isUpdatingTo('3.2.0-beta.1'));
    }

    /** @test */
    public function it_can_check_if_package_is_updating_past_a_specific_beta_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', 'v3.1.0-beta.2', $this->lockPath);

        $script = new UpdatePermissions;

        $this->assertFalse($script->isUpdatingTo('4.0.0'));
        $this->assertFalse($script->isUpdatingTo('4.0'));
        $this->assertFalse($script->isUpdatingTo('3.0.25'));
        $this->assertTrue($script->isUpdatingTo('3.0.26'));
        $this->assertTrue($script->isUpdatingTo('3.1.0-beta.1'));
        $this->assertTrue($script->isUpdatingTo('3.1.0-beta.2'));
        $this->assertFalse($script->isUpdatingTo('3.1.0-beta.3'));
        $this->assertFalse($script->isUpdatingTo('3.1.0'));
    }

    /** @test */
    public function it_runs_update_scripts()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        UpdatePermissions::register();
        UpdateTrees::register();
        UpdateTaxonomies::register();

        $registered = app('statamic.update-scripts');

        UpdateScript::runAll();

        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);

        $this->assertTrue(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertTrue(cache()->has('taxonomies-update-successful'));
    }

    /** @test */
    public function it_deletes_previous_lock_file_after_running_update_scripts()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $this->assertFileExists($this->previousLockPath);

        UpdateScript::runAll();

        $this->assertFileNotExists($this->previousLockPath);
    }

    /** @test */
    public function it_doesnt_error_when_attempting_to_run_update_scripts_with_no_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->lockPath);

        UpdatePermissions::register();
        UpdateTrees::register();
        UpdateTaxonomies::register();
        SeoProUpdate::register();

        $registered = app('statamic.update-scripts');

        UpdateScript::runAll();

        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertFalse(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
    }

    /** @test */
    public function it_doesnt_error_when_attempting_to_run_update_scripts_with_no_previous_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->previousLockPath);

        UpdatePermissions::register();
        UpdateTrees::register();
        UpdateTaxonomies::register();
        SeoProUpdate::register();

        $registered = app('statamic.update-scripts');

        UpdateScript::runAll();

        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertFalse(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
    }

    /** @test */
    public function it_doesnt_error_when_attempting_to_update_on_a_package_doesnt_exist_in_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        UpdatePermissions::register();
        UpdateTrees::register();
        UpdateTaxonomies::register();
        SeoProUpdate::register();

        $registered = app('statamic.update-scripts');

        UpdateScript::runAll();

        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        $this->assertTrue(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertTrue(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
    }

    /** @test */
    public function it_can_write_to_console_from_update_method()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $users = new UserCollection([
            Facades\User::make()->email('jack@jill.com'),
            Facades\User::make()->email('jill@jack.com'),
        ]);

        Facades\User::shouldReceive('all')->andReturn($users);

        app()->instance('statamic.update-scripts', collect()); // Ignore core update scripts.

        UpdatePermissions::register();

        $console = $this->mock(Command::class, function ($mock) {
            $mock->shouldReceive('info')->once()->with('Running update script <comment>['.UpdatePermissions::class.']</comment>');
            $mock->shouldReceive('success')->times(2);
        });

        UpdateScript::runAll($console);
    }

    /** @test */
    public function it_runs_update_scripts_from_specific_package_versions()
    {
        PackToTheFuture::generateComposerLockForMultiple([
            'statamic/cms' => '3.1.8',
            'statamic/seo-pro' => '2.1.0',
        ], $this->lockPath);

        $this->assertFileNotExists($this->previousLockPath);

        UpdatePermissions::register();
        SeoProUpdate::register();

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));

        $registered = app('statamic.update-scripts');

        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        UpdateScript::runAllFromSpecificPackageVersion('statamic/cms', '3.1.8');

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
        $this->assertFileNotExists($this->previousLockPath);

        UpdateScript::runAllFromSpecificPackageVersion('statamic/cms', '3.0.0');

        $this->assertTrue(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
        $this->assertFileNotExists($this->previousLockPath);

        cache()->forget('permissions-update-successful');

        UpdateScript::runAllFromSpecificPackageVersion('statamic/seo-pro', '1.0.0');

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertTrue(cache()->has('seo-pro-update-successful'));
        $this->assertFileNotExists($this->previousLockPath);
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
        return $this->isUpdatingTo('3.1.0');
    }

    public function update()
    {
        Facades\User::all()->map->email()->each(function ($user) {
            $this->console()->success("User [{$user}] permission updated successfully!");
        });

        cache()->put('permissions-update-successful', true);
    }
}

class UpdateTrees extends UpdateScript
{
    public function package()
    {
        return 'statamic/cms';
    }

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return false;
    }

    public function update()
    {
        cache()->put('trees-update-successful', true);
    }
}

class UpdateTaxonomies extends UpdateScript
{
    public function package()
    {
        return 'statamic/cms';
    }

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return true;
    }

    public function update()
    {
        cache()->put('taxonomies-update-successful', true);
    }
}

class SeoProUpdate extends UpdateScript
{
    public function package()
    {
        return 'statamic/seo-pro';
    }

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.1.0');
    }

    public function update()
    {
        cache()->put('seo-pro-update-successful', true);
    }
}
