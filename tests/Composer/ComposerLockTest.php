<?php

namespace Tests\Composer;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Composer\Lock;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\Exceptions\ComposerLockPackageNotFoundException;
use Statamic\Facades\Path;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class ComposerLockTest extends TestCase
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
    }

    public function tearDown(): void
    {
        $this->removeLockFiles();

        parent::tearDown();
    }

    #[Test]
    public function it_can_check_if_lock_file_exists()
    {
        $this->assertFalse(Lock::file()->exists());

        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->lockPath);

        $this->assertTrue(Lock::file()->exists());
    }

    #[Test]
    public function it_can_check_if_custom_lock_file_exists()
    {
        $this->assertFalse(Lock::file($this->previousLockPath)->exists());

        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->previousLockPath);

        $this->assertTrue(Lock::file($this->previousLockPath)->exists());
    }

    #[Test]
    public function it_can_ensure_lock_file_exists()
    {
        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage('Could not find a composer lock file at ['.Path::makeRelative($this->lockPath).'].');

        Lock::file()->ensureExists();
    }

    #[Test]
    public function it_can_ensure_custom_lock_file_exists()
    {
        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage('Could not find a composer lock file at ['.Path::makeRelative($this->previousLockPath).'].');

        Lock::file($this->previousLockPath)->ensureExists();
    }

    #[Test]
    public function it_can_delete_lock_file()
    {
        $this->assertFalse(Lock::file($this->previousLockPath)->exists());

        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->previousLockPath);

        $this->assertTrue(Lock::file($this->previousLockPath)->exists());

        Lock::file($this->previousLockPath)->delete();

        $this->assertFalse(Lock::file($this->previousLockPath)->exists());
    }

    #[Test]
    public function it_errors_when_composer_lock_file_is_not_found()
    {
        $this->expectException(ComposerLockFileNotFoundException::class);
        $this->expectExceptionMessage('Could not find a composer lock file at ['.Path::makeRelative($this->lockPath).'].');

        Lock::file()->getInstalledVersion('package/two');
    }

    #[Test]
    public function it_can_check_if_package_is_installed()
    {
        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->lockPath);

        $this->assertTrue(Lock::file()->isPackageInstalled('package/one'));
        $this->assertFalse(Lock::file()->isDevPackageInstalled('package/one'));
    }

    #[Test]
    public function it_can_check_if_package_is_installed_as_dev_dependency()
    {
        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->lockPath, true);

        $this->assertTrue(Lock::file()->isPackageInstalled('package/one'));
        $this->assertTrue(Lock::file()->isDevPackageInstalled('package/one'));
    }

    #[Test]
    public function it_can_get_installed_version_of_a_package_from_composer_lock()
    {
        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->lockPath);

        $this->assertEquals('1.0.0', Lock::file()->getInstalledVersion('package/one'));

        PackToTheFuture::generateComposerLock('package/one', '1.1.0', $this->lockPath);

        $this->assertEquals('1.1.0', Lock::file()->getInstalledVersion('package/one'));
    }

    #[Test]
    public function it_errors_when_package_is_not_found()
    {
        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->lockPath);

        $this->expectException(ComposerLockPackageNotFoundException::class);
        $this->expectExceptionMessage('Could not find the [package/two] in your composer.lock file.');

        Lock::file()->getInstalledVersion('package/two');
    }

    #[Test]
    public function it_can_gets_normalized_version_of_a_package_from_composer_lock()
    {
        PackToTheFuture::generateComposerLockForMultiple([
            'package/one' => '1.0.0',
            'package/two' => '2.0',
            'package/three' => '3',
            'package/four' => 'v4.0.0',
            'package/five' => 'v5.0',
            'package/six' => 'v6',
            'package/seven' => '7.1.0-beta.1',
            'package/eight' => 'v8.1.0-beta.1',
        ], $this->lockPath);

        $this->assertEquals('1.0.0.0', Lock::file()->getNormalizedInstalledVersion('package/one'));
        $this->assertEquals('2.0.0.0', Lock::file()->getNormalizedInstalledVersion('package/two'));
        $this->assertEquals('3.0.0.0', Lock::file()->getNormalizedInstalledVersion('package/three'));
        $this->assertEquals('4.0.0.0', Lock::file()->getNormalizedInstalledVersion('package/four'));
        $this->assertEquals('5.0.0.0', Lock::file()->getNormalizedInstalledVersion('package/five'));
        $this->assertEquals('6.0.0.0', Lock::file()->getNormalizedInstalledVersion('package/six'));
        $this->assertEquals('7.1.0.0-beta1', Lock::file()->getNormalizedInstalledVersion('package/seven'));
        $this->assertEquals('8.1.0.0-beta1', Lock::file()->getNormalizedInstalledVersion('package/eight'));
    }

    #[Test]
    public function it_can_get_installed_version_of_a_package_from_multiple_composer_lock_files()
    {
        PackToTheFuture::generateComposerLock('package/one', '2.0.0', $this->lockPath);
        PackToTheFuture::generateComposerLock('package/one', '1.0.0', $this->previousLockPath);

        $this->assertEquals('2.0.0', Lock::file()->getInstalledVersion('package/one'));
        $this->assertEquals('1.0.0', Lock::file($this->previousLockPath)->getInstalledVersion('package/one'));
    }

    #[Test]
    public function it_can_override_package_version()
    {
        PackToTheFuture::generateComposerLockForMultiple([
            'package/one' => '1.0.0',
            'package/two' => '1.0.0',
            'statamic/cms' => '3.1.0',
        ], $this->previousLockPath);

        $lock = Lock::file($this->previousLockPath)
            ->overridePackageVersion('package/one', '1.0.1')
            ->overridePackageVersion('statamic/cms', '3.0.0');

        $this->assertEquals('1.0.1', $lock->getInstalledVersion('package/one'));
        $this->assertEquals('1.0.0', $lock->getInstalledVersion('package/two'));
        $this->assertEquals('3.0.0', $lock->getInstalledVersion('statamic/cms'));
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
