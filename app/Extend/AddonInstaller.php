<?php

namespace Statamic\Extend;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Extend\Marketplace;

class AddonInstaller
{
    /**
     * Get installable addons.
     */
    public function installable()
    {
        return $this->approvedAddons()->diff($this->installed());
    }

    /**
     * List installed addons.
     */
    public function installed()
    {
        return Composer::installed()
            ->keys()
            ->intersect($this->approvedAddons())
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
            // throw new \Exception("{$addon} is not an installable package");
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
            // throw new \Exception("{$addon} is not an uninstallable package");
        }

        return Composer::remove($addon);
    }

    /**
     * Get approved addon repositories.
     *
     * @return Illuminate\Support\Collection
     */
    protected function approvedAddons()
    {
        return collect(Marketplace::approvedAddons()['data'])
            ->pluck('variants.*.githubRepo')
            ->flatten()
            ->unique();
    }
}
