<?php

namespace Statamic\UpdateScripts;

use Composer\Semver\VersionParser;
use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Composer\Lock;
use Statamic\Console\NullConsole;

abstract class UpdateScript
{
    const BACKUP_PATH = 'storage/statamic/updater/composer.lock.bak';

    protected $package;
    protected $console;
    protected $files;

    /**
     * Instantiate update script.
     */
    public function __construct($package, $console = null)
    {
        $this->package = $package;
        $this->console = $console ?? new NullConsole;
        $this->files = app(Filesystem::class);
    }

    /**
     * Whether the update should be run.
     *
     * @param  string  $newVersion
     * @param  string  $oldVersion
     * @return bool
     */
    abstract public function shouldUpdate($newVersion, $oldVersion);

    /**
     * Perform the update.
     */
    abstract public function update();

    /**
     * Get the package being updated.
     *
     * @return string
     */
    public function package()
    {
        return $this->package;
    }

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
     * @param  mixed  $version
     * @return bool
     */
    public function isUpdatingTo($version)
    {
        $version = (new VersionParser)->normalize($version);
        $newVersion = Lock::file()->getNormalizedInstalledVersion($this->package());
        $oldVersion = Lock::file(self::BACKUP_PATH)->getNormalizedInstalledVersion($this->package());

        return version_compare($version, $newVersion, '<=') && version_compare($version, $oldVersion, '>');
    }

    /**
     * Register update script with Statamic.
     */
    public static function register($package)
    {
        if (! app()->has('statamic.update-scripts')) {
            return;
        }

        app('statamic.update-scripts')[] = [
            'class' => static::class,
            'package' => $package,
        ];
    }
}
