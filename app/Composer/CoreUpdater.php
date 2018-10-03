<?php

namespace Statamic\Composer;

use Facades\App\Services\Composer;
use Facades\App\Services\CoreChangelog;

class CoreUpdater
{
    public $core = 'statamic/cms';

    /**
     * Get current version.
     */
    public function currentVersion()
    {
        return Composer::installed()->get($this->core)->version;
    }

    /**
     * Get latest version.
     */
    public function latestVersion()
    {
        return CoreChangelog::get()->keys()->first();
    }

    /**
     * Update core to latest constrained version.
     */
    public function update()
    {
        return Composer::update($this->core);
    }

    /**
     * Update to latest version.
     */
    public function updateToLatest()
    {
        return Composer::require($this->core, $this->latestVersionConstraint());
    }

    /**
     * Install explicit version.
     *
     * @param string $version
     */
    public function installExplicitVersion(string $version)
    {
        return Composer::require($this->core, $version);
    }

    /**
     * Get latest version and assemble recommended latest version constraint.
     *
     * @return string
     */
    private function latestVersionConstraint()
    {
        $versionParts = collect(explode('.', $this->latestVersion()));
        $versionParts->pop();
        $versionParts->push('*');

        return $versionParts->implode('.');
    }
}
