<?php

namespace Statamic\Composer;

use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\Marketplace;

class AddonInstaller
{
    /**
     * @var \Illuminate\Support\Collection;
     */
    public $approvedAddons;

    /**
     * Instantiate addon installer.
     */
    public function __construct()
    {
        $this->approvedAddons = Marketplace::approvedAddons();
    }

    /**
     * Get installable addons.
     */
    public function installable()
    {
        return $this->approvedAddons->diff($this->installed());
    }

    /**
     * List installed addons.
     */
    public function installed()
    {
        return Composer::installed()
            ->keys()
            ->intersect($this->approvedAddons)
            ->values();
    }

    /**
     * Install an addon.
     *
     * @param string $addon
     */
    public function install(string $addon)
    {
        if (! $this->installable()->contains($addon)) {
            throw new \Exception("{$addon} is not an installable package");
        }

        return Composer::require($addon);
    }

    /**
     * Uninstall an addon.
     *
     * @param string $addon
     */
    public function uninstall(string $addon)
    {
        if (! $this->installed()->contains($addon)) {
            throw new \Exception("{$addon} is not an uninstallable package");
        }

        return Composer::remove($addon);
    }
}
