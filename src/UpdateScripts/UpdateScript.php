<?php

namespace Statamic\UpdateScripts;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Composer\Lock;
use Statamic\Console\NullConsole;

abstract class UpdateScript
{
    const BACKUP_PATH = 'storage/statamic/updater/composer.lock.bak';

    protected $console;
    protected $files;

    /**
     * Instantiate update script.
     */
    public function __construct($console = null)
    {
        $this->console = $console ?? new NullConsole;
        $this->files = app(Filesystem::class);
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
     * @return bool
     */
    public function isUpdatingTo($version)
    {
        $newVersion = Lock::file()->getInstalledVersion($this->package());
        $oldVersion = Lock::file(self::BACKUP_PATH)->getInstalledVersion($this->package());

        return version_compare($version, $newVersion, '<=') && version_compare($version, $oldVersion, '>');
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
