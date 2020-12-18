<?php

namespace Statamic\UpdateScripts;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Composer\Lock;
use Statamic\Console\NullConsole;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\Exceptions\ComposerLockPackageNotFoundException;

abstract class UpdateScript
{
    protected $console;
    protected $files;

    /**
     * Instantiate update script.
     */
    public function __construct($console = null)
    {
        $this->console = $console ?? new NullConsole;
        $this->files = app(Filesystem::class);

        $this->ensureComposerLockFileExists('composer.lock');
        $this->ensureComposerLockFileExists('storage/statamic/updater/composer.lock.bak');
    }

    /**
     * Define the package being updated.
     *
     * @return string
     */
    abstract public function package();

    /**
     * Whether the update should be run.
     *
     * @param string $newVersion
     * @param string $oldVersion
     * @return bool
     */
    abstract public function shouldUpdate($newVersion, $oldVersion);

    /**
     * Perform the update.
     */
    abstract public function update();

    /**
     * Get console command object for outputting messages to console.
     *
     * @return \Illuminate\Console\Command|NullConsole
     */
    public function console()
    {
        return $this->console;
    }

    /**
     * Determine if user is updating to specific version.
     *
     * @param mixed $version
     */
    public function isUpdatingTo($version)
    {
        $oldVersion = Lock::file(storage_path('statamic/updater/composer.lock.bak'))
            ->getInstalledVersion($this->package());

        return version_compare($version, $oldVersion, '>');
    }

    /**
     * Ensure lock files exist for version checks.
     *
     * @param string $relativePath
     * @return bool
     */
    protected function ensureComposerLockFileExists($relativePath)
    {
        if (! Lock::file($relativePath)->exists()) {
            throw new ComposerLockFileNotFoundException(base_path($relativePath));
        }
    }

    /**
     * Register update script with Statamic.
     */
    public static function register()
    {
        if (! app()->has('statamic.update-scripts')) {
            return;
        }

        app('statamic.update-scripts')[] = static::class;
    }

    /**
     * Run all registered update scripts.
     *
     * @param mixed $console
     */
    public static function runAll($console = null)
    {
        $newLockFile = Lock::file();
        $oldLockFile = Lock::file(storage_path('statamic/updater/composer.lock.bak'));

        app('statamic.update-scripts')
            ->map(function ($fqcn) use ($console) {
                try {
                    return new $fqcn($console);
                } catch (ComposerLockFileNotFoundException $exception) {
                    return null;
                }
            })
            ->filter()
            ->filter(function ($script) use ($newLockFile, $oldLockFile) {
                try {
                    return $script->shouldUpdate(
                        $newLockFile->getInstalledVersion($script->package()),
                        $oldLockFile->getInstalledVersion($script->package())
                    );
                } catch (ComposerLockPackageNotFoundException $exception) {
                    return false;
                }
            })
            ->each(function ($script) {
                $script->console()->info('Running update script <comment>['.get_class($script).']</comment>');
                $script->update();
            });
    }
}
