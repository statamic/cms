<?php

namespace Statamic\Updater;

use Facades\Statamic\Console\Processes\Composer;

class Updater
{
    /**
     * @var string
     */
    protected $package;

    /**
     * Instantiate package updater.
     *
     * @param  string  $package
     */
    public function __construct(string $package)
    {
        $this->package = $package;
    }

    /**
     * Instantiate package updater.
     *
     * @param  string  $package
     * @return static
     */
    public static function package(string $package)
    {
        return new static($package);
    }

    /**
     * Install explicit version.
     *
     * @param  string  $version
     */
    public function install(string $version)
    {
        return Composer::require($this->package, $version);
    }
}
