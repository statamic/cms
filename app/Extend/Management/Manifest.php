<?php

namespace Statamic\Extend\Management;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

class Manifest extends PackageManifest
{
    public function build()
    {
        $packages = [];

        if ($this->files->exists($path = $this->vendorPath.'/composer/installed.json')) {
            $packages = json_decode($this->files->get($path), true);
        }

        $this->write(collect($packages)->filter(function ($package) {
            return array_get($package, 'type') === 'statamic-addon';
        })->keyBy('name')->map(function ($package) {
            return $this->formatPackage($package);
        })->all());

        $this->getManifest();
    }

    protected function formatPackage($package)
    {
        $provider = $package['extra']['laravel']['providers'][0];
        $namespace = join('\\', explode('\\', $provider, -1));

        return [
            'namespace' => $namespace,
        ];
    }
}