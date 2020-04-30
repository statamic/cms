<?php

namespace Tests\Fakes\Composer;

class Composer
{
    public $installed;

    public function __construct()
    {
        $this->installed = collect();

        $this->putPackageIntoInstalled('base/install');
    }

    public function installed()
    {
        return collect($this->installed);
    }

    public function installedVersion(string $package)
    {
        return $this->installed()->get($package)->version;
    }

    public function require(string $package, string $version = null)
    {
        $this->putPackageIntoInstalled($package, $version);
    }

    public function remove(string $package)
    {
        $this->installed->forget($package);
    }

    public function update(string $package)
    {
        $this->putPackageIntoInstalled($package, $this->incrementVersion($this->installed->get($package)->version));
    }

    public function cachedOutput(string $package)
    {
        //
    }

    public function lastCompletedCachedOutput(string $package)
    {
        //
    }

    public function clearCachedOutput(string $package)
    {
        //
    }

    private function putPackageIntoInstalled($package, $version = null)
    {
        if (is_null($version) && $this->installed->has($package)) {
            $version = $this->installed->get($package)->version;
        }

        // Just like actual composer would resolve an x.x.* constraint to an actual version number,
        // Our fake composer instance will resolve to x.x.9 for the purposes of testing.
        if (str_contains($version, '*')) {
            $version = str_replace('*', '9', $version);
        }

        $this->installed->put($package, (object) [
            'name' => $package,
            'version' => $version ?? '1.0.0',
            'description' => 'This is a fake package.',
        ]);
    }

    private function incrementVersion(string $version)
    {
        $versionParts = collect(explode('.', $version));
        $incrementedLastPart = $versionParts->last() + 1;
        $versionParts->pop();

        return $versionParts->push($incrementedLastPart)->implode('.');
    }
}
