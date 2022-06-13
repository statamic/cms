<?php

namespace Statamic\Extend;

use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Foundation\PackageManifest;
use ReflectionClass;
use Statamic\Facades\File;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Manifest extends PackageManifest
{
    public function build()
    {
        $this->manifest = null;

        $packages = [];

        if ($this->files->exists($path = $this->vendorPath.'/composer/installed.json')) {
            $installed = json_decode($this->files->get($path), true);
            $packages = $installed['packages'] ?? $installed;
        }

        $this->write(collect($packages)->filter(function ($package) {
            return Arr::has($package, 'extra.statamic');
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
        $namespace = implode('\\', $providerParts);

        $autoload = $package['autoload']['psr-4'][$namespace.'\\'];
        $directory = Str::removeRight(dirname($reflector->getFileName()), rtrim($autoload, '/'));

        $json = json_decode(File::get($directory.'/composer.json'), true);
        $statamic = $json['extra']['statamic'] ?? [];
        $author = $json['authors'][0] ?? null;

        $marketplaceData = Marketplace::package($package['name'], $package['version']);

        return [
            'id' => $package['name'],
            'slug' => $statamic['slug'] ?? null,
            'editions' => $statamic['editions'] ?? [],
            'marketplaceId' => data_get($marketplaceData, 'id', null),
            'marketplaceSlug' => data_get($marketplaceData, 'slug', null),
            'marketplaceUrl' => data_get($marketplaceData, 'url', null),
            'marketplaceSellerSlug' => data_get($marketplaceData, 'seller', null),
            'latestVersion' => data_get($marketplaceData, 'latest_version', null),
            'version' => Str::removeLeft($package['version'], 'v'),
            'namespace' => $namespace,
            'autoload' => $autoload,
            'provider' => $provider,

            // Local data for marketplace GUI?
            'name' => $statamic['name'] ?? Arr::last($providerParts),
            'url' => $statamic['url'] ?? null,
            'description' => $statamic['description'] ?? $package['description'] ?? null,
            'developer' => $statamic['developer'] ?? $author['name'] ?? null,
            'developerUrl' => $statamic['developer-url'] ?? $author['homepage'] ?? null,
            'email' => $package['support']['email'] ?? null,
        ];
    }

    public function addons()
    {
        return collect($this->getManifest());
    }
}
