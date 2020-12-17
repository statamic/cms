<?php

namespace Statamic\UpdateScripts;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Composer\Lock;
use Statamic\Exceptions\ComposerLockFileNotFoundException;

abstract class UpdateScript
{
    /**
     * Instantiate update script.
     */
    public function __construct()
    {
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
}
