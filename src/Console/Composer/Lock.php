<?php

namespace Statamic\Console\Composer;

use Illuminate\Filesystem\Filesystem;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\Exceptions\ComposerLockPackageNotFoundException;
use Statamic\Facades\Path;

class Lock
{
    public $files;
    public $path;

    /**
     * Instantiate lock file helper.
     *
     * @param string $file
     */
    public function __construct(string $file = 'composer.lock')
    {
        $this->files = app(Filesystem::class);

        $this->path = Path::isAbsolute($file) ? $file : base_path($file);
    }

    /**
     * Instantiate lock file helper.
     *
     * @param string $file
     * @return static
     */
    public static function file(string $file = 'composer.lock')
    {
        return new static($file);
    }

    /**
     * Backup lock file, using vanilla PHP so that this can be run in a Composer hook.
     *
     * @param string $file
     */
    public static function backup(string $file = 'composer.lock')
    {
        if (! is_file($file)) {
            return;
        }

        $backup = dirname($file).'/storage/statamic/updater/composer.lock.bak';

        if (! is_dir($backupDir = dirname($backup))) {
            mkdir($backupDir, 0777, true);
        }

        copy($file, $backup);
    }

    /**
     * Determine if lock file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->files->exists($this->path);
    }

    /**
     * Get installed version of a specific package.
     *
     * @param string $package
     * @return string
     */
    public function getInstalledVersion(string $package)
    {
        if (! $this->exists()) {
            throw new ComposerLockFileNotFoundException($this->path);
        }

        $installed = collect(json_decode($this->files->get($this->path))->packages)
            ->keyBy('name')
            ->get($package);

        if (! $installed) {
            throw new ComposerLockPackageNotFoundException($package);
        }

        return $this->normalizeVersion($installed->version);
    }

    /**
     * Sometimes composer returns versions with a 'v', sometimes it doesn't.
     *
     * @param string $version
     * @return string
     */
    private function normalizeVersion(string $version)
    {
        return ltrim($version, 'v');
    }
}
