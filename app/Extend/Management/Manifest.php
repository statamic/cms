<?php

namespace Statamic\Extend\Management;

use ReflectionClass;
use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\API\File;
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
        })->filter()->all());

        $this->getManifest();
    }

    protected function formatPackage($package)
    {
        if (! $provider = $package['extra']['laravel']['providers'][0] ?? null) {
            return;
        }

        $reflector = new ReflectionClass($provider);
        $providerParts = explode('\\', $provider, -1);
        $namespace = join('\\', $providerParts);

        $autoload = $package['autoload']['psr-4'][$namespace.'\\'];
        $directory = Str::removeRight(dirname($reflector->getFileName()), $autoload);

        $json = json_decode(File::get($directory.'/composer.json'), true);
        $statamic = $json['extra']['statamic'] ?? [];
        $author = $json['authors'][0] ?? null;

        return [
            'id' => $id = Arr::last($providerParts),
            'package' => $package['name'],
            'name' => $statamic['name'] ?? $id,
            'description' => $statamic['description'] ?? $package['description'] ?? null,
            'namespace' => $namespace,
            'directory' => $directory,
            'autoload' => $autoload,
            'url' => $statamic['url'] ?? null,
            'developer' => $statamic['developer'] ?? $author['name'] ?? null,
            'developerUrl' => $statamic['developer-url'] ?? $author['homepage'] ?? null,
            'email' => $package['support']['email'] ?? null,
            'version' => $package['version'],
        ];
    }

    public function addons()
    {
        return collect($this->getManifest());
    }
}