<?php

namespace Statamic\Extend;

use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Facade;
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

        return [
            'id' => $package['name'],
            'slug' => $statamic['slug'] ?? null,
            'editions' => $statamic['editions'] ?? [],
            'version' => Str::removeLeft($package['version'], 'v'),
            'raw_version' => $package['version'],
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

    public function fetchPackageDataFromMarketplace()
    {
        $packages = $this->addons()
            ->map(function (array $package) {
                return [
                    'package' => $package['id'],
                    'version' => $package['raw_version'],
                    'edition' => config('statamic.editions.addons.'.$package['id']),
                ];
            })
            ->values()
            ->all();

        $marketplaceData = Marketplace::packages($packages);

        $this->write($this->manifest = $this->addons()->map(function (array $package) use ($marketplaceData) {
            $marketplaceData = $marketplaceData->get($package['id']);

            return [
                ...$package,
                'marketplaceId' => data_get($marketplaceData, 'id'),
                'marketplaceSlug' => data_get($marketplaceData, 'slug'),
                'marketplaceUrl' => data_get($marketplaceData, 'url'),
                'marketplaceSellerSlug' => data_get($marketplaceData, 'seller'),
                'isCommercial' => data_get($marketplaceData, 'is_commercial', false),
                'latestVersion' => data_get($marketplaceData, 'latest_version'),
            ];
        })->all());

        Facade::clearResolvedInstance(AddonRepository::class);
    }
}
