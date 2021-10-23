<?php

namespace Statamic\Extend;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Marketplace\Marketplace;

class AddonInstaller
{
    /**
     * Check if an addon is installable.
     */
    private function isInstallable(string $package)
    {
        return (bool) Marketplace::package($package);
    }

    /**
     * Check if an addon is installed.
     */
    private function isInstalled(string $package)
    {
        return Composer::installed()->has($package);
    }

    /**
     * Install an addon.
     *
     * @param  string  $addon
     */
    public function install(string $addon)
    {
        if (! $this->isInstallable($addon)) {
            throw new \Exception("{$addon} is not an installable package.");
        }

        return Composer::require($addon);
    }

    /**
     * Uninstall an addon.
     *
     * @param  string  $addon
     */
    public function uninstall(string $addon)
    {
        if (! $this->isInstalled($addon)) {
            throw new \Exception("{$addon} is not installed.");
        }

        return Composer::remove($addon);
    }
}
