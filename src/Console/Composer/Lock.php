<?php

namespace Statamic\Console\Composer;

use Composer\Package\Version\VersionParser;
use Illuminate\Filesystem\Filesystem;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\Exceptions\ComposerLockPackageNotFoundException;
use Statamic\Facades\Path;
use Statamic\UpdateScripts\UpdateScript;

class Lock
{
    protected $files;
    protected $path;

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

        $backupPath = dirname($file).'/'.UpdateScript::BACKUP_PATH;

        if (! is_dir($backupDir = dirname($backupPath))) {
            mkdir($backupDir, 0777, true);
        }

        copy($file, $backupPath);
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
     * Delete lock file.
     */
    public function delete()
    {
        $this->files->delete($this->path);
    }

    /**
     * Ensure this lock file exists.
     *
     * @throws ComposerLockFileNotFoundException
     * @return $this
     */
    public function ensureExists()
    {
        throw_unless($this->exists(), new ComposerLockFileNotFoundException(Path::makeRelative($this->path)));

        return $this;
    }

    /**
     * Get installed version of a specific package.
     *
     * @param string $package
     * @return string
     */
    public function getInstalledVersion(string $package)
    {
        $this->ensureExists();

        $installed = collect(json_decode($this->files->get($this->path))->packages)
            ->keyBy('name')
            ->get($package);

        if (! $installed) {
            throw new ComposerLockPackageNotFoundException($package);
        }

        return $installed->version;
    }

    /**
     * Get installed version of a specific package, normalized for comparisons.
     *
     * @param string $package
     * @return string
     */
    public function getNormalizedInstalledVersion(string $package)
    {
        return (new VersionParser)->normalize($this->getInstalledVersion($package));
    }

    /**
     * Check if package is installed.
     *
     * @param string $package
     * @return bool
     */
    public function isInstalled(string $package)
    {
        $this->ensureExists();

        return collect(json_decode($this->files->get($this->path))->packages)
            ->keyBy('name')
            ->has($package);
    }

    /**
     * Override package version.
     *
     * @param string $package
     * @param string $version
     * @return $this
     */
    public function overridePackageVersion($package, $version)
    {
        $content = json_decode($this->files->get($this->path), true);

        $packages = collect($content['packages'])
            ->map(function ($packageDetails) use ($package, $version) {
                if ($packageDetails['name'] === $package) {
                    $packageDetails['version'] = $version;
                }

                return $packageDetails;
            })
            ->all();

        $content['packages'] = $packages;

        $this->files->put($this->path, json_encode($content, JSON_UNESCAPED_SLASHES));

        return $this;
    }
}
