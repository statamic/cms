<?php

namespace Tests\Composer;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Composer\Lock;

/**
 * Test that we can backup a composer lock file using vanilla PHP so that it can be run in a Composer hook.
 */
class ComposerLockBackupTest extends \PHPUnit\Framework\TestCase
{
    protected $lockPath = './composer.lock';
    protected $customLockPath = './custom/composer.lock';
    protected $backupLockPath = './storage/statamic/updater/composer.lock.bak';
    protected $customBackupLockPath = './custom/storage/statamic/updater/composer.lock.bak';

    public function setUp(): void
    {
        parent::setUp();

        $this->removeLockFiles();
    }

    public function tearDown(): void
    {
        $this->removeLockFiles();

        parent::tearDown();
    }

    #[Test]
    public function it_can_backup_existing_lock_file()
    {
        file_put_contents($this->lockPath, $content = 'test lock file content');

        $this->assertFileExists($this->lockPath);
        $this->assertFileDoesNotExist($this->backupLockPath);

        Lock::backup();

        $this->assertFileExists($this->backupLockPath);
        $this->assertEquals($content, file_get_contents($this->backupLockPath));
    }

    #[Test]
    public function it_doesnt_throw_exception_when_attempting_to_backup_non_existend_lock_file()
    {
        Lock::backup('non-existent-file.lock');

        $this->assertFileDoesNotExist($this->backupLockPath);
    }

    #[Test]
    public function it_can_backup_lock_file_from_custom_location()
    {
        if (! is_dir($dir = './custom')) {
            mkdir($dir);
        }

        file_put_contents($this->customLockPath, $content = 'custom lock file content');

        $this->assertFileExists($this->customLockPath);
        $this->assertFileDoesNotExist($this->customBackupLockPath);

        Lock::backup($this->customLockPath);

        $this->assertFileExists($this->customBackupLockPath);
        $this->assertEquals($content, file_get_contents($this->customBackupLockPath));
    }

    private function removeLockFiles()
    {
        $files = [
            $this->lockPath,
            $this->customLockPath,
            $this->backupLockPath,
            $this->customBackupLockPath,
        ];

        foreach ($files as $lockFile) {
            if (is_file($lockFile)) {
                unlink($lockFile);
            }
        }
    }
}
