<?php

namespace Tests\Composer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Composer\Lock;
use Symfony\Component\Process\Process;

/**
 * Test that we can backup a composer lock file using vanilla PHP so that it can be run in a Composer hook.
 */
class ComposerLockBackupTest extends \PHPUnit\Framework\TestCase
{
    protected $lockPath = './composer.lock';
    protected $envLockPath = './composer.testing.lock';
    protected $customLockPath = './custom/composer.lock';
    protected $backupLockPath = './storage/statamic/updater/composer.lock.bak';
    protected $customBackupLockPath = './custom/storage/statamic/updater/composer.lock.bak';
    protected $tempDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->removeLockFiles();
    }

    public function tearDown(): void
    {
        $this->removeLockFiles();
        $this->removeTempDir();

        unset($_ENV['COMPOSER']);

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
    public function it_backs_up_the_lock_file_named_by_the_composer_env_var()
    {
        $_ENV['COMPOSER'] = 'composer.testing.json';

        file_put_contents($this->lockPath, 'default lock file content');
        file_put_contents($this->envLockPath, $content = 'env lock file content');

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

    #[Test]
    #[DataProvider('composerScriptContextProvider')]
    public function it_can_backup_the_lock_file_in_a_composer_script_context($composerEnv, $expected)
    {
        $dir = $this->makeTempDir();

        file_put_contents($dir.'/composer.lock', 'default lock file content');
        file_put_contents($dir.'/composer.testing.lock', 'env lock file content');

        // Composer builds a class autoloader for script events but never runs the `autoload.files`
        // entries, so none of Laravel's or Statamic's helper functions exist. Replicate that here
        // to ensure nothing reachable from the hook depends on them.
        $script = str_replace('{{ vendor }}', realpath(__DIR__.'/../../vendor'), <<<'EOT'
require '{{ vendor }}/composer/ClassLoader.php';

$loader = new Composer\Autoload\ClassLoader;

foreach (require '{{ vendor }}/composer/autoload_psr4.php' as $namespace => $paths) {
    $loader->setPsr4($namespace, $paths);
}

$loader->addClassMap(require '{{ vendor }}/composer/autoload_classmap.php');
$loader->register();

Statamic\Console\Composer\Lock::backup();
EOT);

        $process = new Process(['php', '-r', $script], $dir, ['COMPOSER' => $composerEnv]);

        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertEquals($expected, file_get_contents($dir.'/storage/statamic/updater/composer.lock.bak'));
    }

    public static function composerScriptContextProvider()
    {
        return [
            'without the env var' => [false, 'default lock file content'],
            'with the env var' => ['composer.testing.json', 'env lock file content'],
        ];
    }

    private function makeTempDir()
    {
        mkdir($dir = sys_get_temp_dir().'/statamic-composer-hook-'.bin2hex(random_bytes(6)));

        return $this->tempDir = $dir;
    }

    private function removeTempDir($dir = null)
    {
        if (! ($dir ??= $this->tempDir) || ! is_dir($dir)) {
            return;
        }

        foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
            is_dir($path = $dir.'/'.$item) ? $this->removeTempDir($path) : unlink($path);
        }

        rmdir($dir);
    }

    private function removeLockFiles()
    {
        $files = [
            $this->lockPath,
            $this->envLockPath,
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
