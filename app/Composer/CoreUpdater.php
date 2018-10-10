<?php

namespace Statamic\Composer;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Composer\CoreChangelog;
use Statamic\Statamic;

class CoreUpdater
{
    /**
     * Get current version.
     *
     * @return string
     */
    public function currentVersion()
    {
        return Statamic::version();
    }

    /**
     * Get latest version.
     */
    public function latestVersion()
    {
        return CoreChangelog::latest()->version;
    }

    /**
     * Update core to latest constrained version.
     */
    public function update()
    {
        return Composer::update(Statamic::CORE_REPO);
    }

    /**
     * Update to latest version.
     */
    public function updateToLatest()
    {
        return Composer::require(Statamic::CORE_REPO, $this->latestVersionConstraint());
    }

    /**
     * Install explicit version.
     *
     * @param string $version
     */
    public function installExplicitVersion(string $version)
    {
        return Composer::require(Statamic::CORE_REPO, $version);
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
