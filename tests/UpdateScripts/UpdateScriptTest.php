<?php

namespace Tests\UpdateScripts;

use Facades\Statamic\UpdateScripts\Manager;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\UserCollection;
use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\UpdateScripts\UpdateScript;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class UpdateScriptTest extends TestCase
{
    private $files;
    private $lockPath;
    private $previousLockPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->lockPath = base_path('composer.lock');
        $this->previousLockPath = storage_path('statamic/updater/composer.lock.bak');

        $this->removeLockFiles();

        app()->instance('statamic.update-scripts', collect());
    }

    public function tearDown(): void
    {
        $this->removeLockFiles();

        parent::tearDown();
    }

    #[Test]
    public function it_can_register_itself_with_statamic()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $this->assertCount(0, $registered = $this->getRegistered());
        $this->assertInstanceOf(Collection::class, $registered);
        $this->assertNotContains(UpdatePermissions::class, $registered);
        $this->assertNotContains(UpdateTrees::class, $registered);

        $this->register(UpdatePermissions::class);
        $this->register(UpdateTrees::class);

        $this->assertCount(2, $registered = $this->getRegistered());
        $this->assertInstanceOf(Collection::class, $registered);
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
    }

    #[Test]
    public function it_silently_fails_to_register_on_older_statamic_versions()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        app()->forgetInstance('statamic.update-scripts');

        $this->assertNull($this->register(UpdatePermissions::class));
    }

    #[Test]
    public function it_can_check_if_package_is_updating_to_specific_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $script = $this->register(UpdatePermissions::class);

        $this->assertTrue($script->isUpdatingTo('3.1.8'));
        $this->assertFalse($script->isUpdatingTo('3.0.25'));
        $this->assertFalse($script->isUpdatingTo('2.0'));
    }

    #[Test]
    public function it_can_check_if_package_is_updating_past_a_specific_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $script = $this->register(UpdatePermissions::class);

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

    #[Test]
    public function it_can_check_if_package_is_updating_past_a_specific_beta_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', 'v3.1.0-beta.2', $this->lockPath);

        $script = $this->register(UpdatePermissions::class);

        $this->assertFalse($script->isUpdatingTo('4.0.0'));
        $this->assertFalse($script->isUpdatingTo('4.0'));
        $this->assertFalse($script->isUpdatingTo('3.0.25'));
        $this->assertTrue($script->isUpdatingTo('3.0.26'));
        $this->assertTrue($script->isUpdatingTo('3.1.0-beta.1'));
        $this->assertTrue($script->isUpdatingTo('3.1.0-beta.2'));
        $this->assertFalse($script->isUpdatingTo('3.1.0-beta.3'));
        $this->assertFalse($script->isUpdatingTo('3.1.0'));
    }

    #[Test]
    public function it_properly_normalizes_the_version_you_pass_in_when_checking_for_updating_to_a_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0-beta.1', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', 'v3.1.0', $this->lockPath);

        $script = $this->register(UpdatePermissions::class);

        $this->assertTrue($script->isUpdatingTo('3.1'));
        $this->assertTrue($script->isUpdatingTo('3.1.0'));
        $this->assertFalse($script->isUpdatingTo('3.1.1'));
    }

    #[Test]
    public function it_can_check_if_version_is_normalized_when_user_overrides_lock_version()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.0', $this->lockPath);

        $script = $this->register(UpdatePermissions::class);

        // When user runs `php please updates:run 3.0`, `isUpdatingTo()` was returning the wrong result
        // in this situation because `3.0` and `3.0.0` are not equal when using `version_compare()`.
        $this->assertFalse($script->isUpdatingTo('3.0.0'));
    }

    #[Test]
    public function it_runs_update_scripts()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $this->register(UpdatePermissions::class);
        $this->register(UpdateTrees::class);
        $this->register(UpdateTaxonomies::class);

        $this->assertCount(3, $registered = $this->getRegistered());
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);

        Manager::runAll();

        $this->assertTrue(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertTrue(cache()->has('taxonomies-update-successful'));
    }

    #[Test]
    public function it_passes_normalized_versions_into_shouldUpdate()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', 'v3.0.25-alpha.2', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', 'v3.1.8', $this->lockPath);

        // The fake update script will call this closure.
        $callbackRan = false;
        app()->instance('version-assertions', function ($newVersion, $oldVersion) use (&$callbackRan) {
            $this->assertEquals('3.1.8.0', $newVersion);
            $this->assertEquals('3.0.25.0-alpha2', $oldVersion);
            $callbackRan = true;
        });

        $this->register(VersionAssertionUpdate::class);

        Manager::runAll();

        $this->assertTrue($callbackRan);
    }

    #[Test]
    public function it_deletes_previous_lock_file_after_running_update_scripts()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $this->assertFileExists($this->previousLockPath);

        Manager::runAll();

        $this->assertFileDoesNotExist($this->previousLockPath);
    }

    #[Test]
    public function it_doesnt_error_when_attempting_to_run_update_scripts_with_no_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->lockPath);

        $this->register(UpdatePermissions::class);
        $this->register(UpdateTrees::class);
        $this->register(UpdateTaxonomies::class);
        $this->register(SeoProUpdate::class, 'statamic/seo-pro');

        $this->assertCount(4, $registered = $this->getRegistered());
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        Manager::runAll();

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertFalse(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
    }

    #[Test]
    public function it_doesnt_error_when_attempting_to_run_update_scripts_with_no_previous_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.0', $this->previousLockPath);

        $this->register(UpdatePermissions::class);
        $this->register(UpdateTrees::class);
        $this->register(UpdateTaxonomies::class);
        $this->register(SeoProUpdate::class, 'statamic/seo-pro');

        $this->assertCount(4, $registered = $this->getRegistered());
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        Manager::runAll();

        $this->assertFalse(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertFalse(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
    }

    #[Test]
    public function it_doesnt_error_when_attempting_to_update_on_a_package_doesnt_exist_in_lock_file()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $this->register(UpdatePermissions::class);
        $this->register(UpdateTrees::class);
        $this->register(UpdateTaxonomies::class);
        $this->register(SeoProUpdate::class, 'statamic/seo-pro');

        $this->assertCount(4, $registered = $this->getRegistered());
        $this->assertContains(UpdatePermissions::class, $registered);
        $this->assertContains(UpdateTrees::class, $registered);
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);

        Manager::runAll();

        $this->assertTrue(cache()->has('permissions-update-successful'));
        $this->assertFalse(cache()->has('trees-update-successful'));
        $this->assertTrue(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
    }

    #[Test]
    public function it_can_write_to_console_from_update_method()
    {
        PackToTheFuture::generateComposerLock('statamic/cms', '3.0.25', $this->previousLockPath);
        PackToTheFuture::generateComposerLock('statamic/cms', '3.1.8', $this->lockPath);

        $users = new UserCollection([
            Facades\User::make()->email('jack@jill.com'),
            Facades\User::make()->email('jill@jack.com'),
        ]);

        Facades\User::shouldReceive('all')->andReturn($users);

        $this->register(UpdatePermissions::class);

        $console = $this->mock(Command::class, function ($mock) {
            $mock->shouldReceive('info')->once()->with('Running update script <comment>['.UpdatePermissions::class.']</comment>');
            $mock->shouldReceive('success')->times(2);
        });

        Manager::runAll($console);
    }

    #[Test]
    public function it_runs_scripts_forspecific_package_versions()
    {
        PackToTheFuture::generateComposerLockForMultiple([
            'statamic/cms' => '3.1.8',
            'statamic/seo-pro' => '2.1.0',
        ], $this->lockPath);

        $this->assertFileDoesNotExist($this->previousLockPath);

        $this->register(UpdateTaxonomies::class);
        $this->register(SeoProUpdate::class, 'statamic/seo-pro');

        $this->assertCount(2, $registered = $this->getRegistered());
        $this->assertContains(UpdateTaxonomies::class, $registered);
        $this->assertContains(SeoProUpdate::class, $registered);
        $this->assertFalse(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));

        Manager::runUpdatesForSpecificPackageVersion('statamic/cms', '3.0.0');

        $this->assertTrue(cache()->has('taxonomies-update-successful'));
        $this->assertFalse(cache()->has('seo-pro-update-successful'));
        $this->assertFileDoesNotExist($this->previousLockPath);

        cache()->forget('taxonomies-update-successful');

        Manager::runUpdatesForSpecificPackageVersion('statamic/seo-pro', '1.0.0');

        $this->assertFalse(cache()->has('taxonomies-update-successful'));
        $this->assertTrue(cache()->has('seo-pro-update-successful'));
        $this->assertFileDoesNotExist($this->previousLockPath);
    }

    private function register($class, $package = 'statamic/cms')
    {
        $class::register($package);

        if (! app()->has('statamic.update-scripts')) {
            return null;
        }

        return new $class($package);
    }

    private function getRegistered()
    {
        return app('statamic.update-scripts')
            ->pluck('class')
            ->reject(function ($class) {
                return Str::startsWith($class, 'Statamic\UpdateScripts\Core');
            })
            ->values();
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
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.1.0');
    }

    public function update()
    {
        cache()->put('seo-pro-update-successful', true);
    }
}

class VersionAssertionUpdate extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        app('version-assertions')($newVersion, $oldVersion);
    }

    public function update()
    {
        //
    }
}
