<?php

namespace Statamic\Extend;

use Facades\Statamic\Extend\Marketplace;
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
            $packages = json_decode($this->files->get($path), true);
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
        $namespace = join('\\', $providerParts);

        $autoload = $package['autoload']['psr-4'][$namespace.'\\'];
        $directory = Str::removeRight(dirname($reflector->getFileName()), $autoload);

        $json = json_decode(File::get($directory.'/composer.json'), true);
        $statamic = $json['extra']['statamic'] ?? [];
        $author = $json['authors'][0] ?? null;

        $marketplaceData = Marketplace::withoutLocalData()->findByPackageName($package['name']);

        $installedVariant = collect(data_get($marketplaceData, 'variants', []))->first(function ($variant) use ($package) {
            return explode('.', $package['version'])[0] == $variant['number'];
        });

        return [
            'id' => $package['name'],
            'slug' => $statamic['slug'] ?? null,
            'marketplaceProductId' => data_get($marketplaceData, 'id', null),
            'marketplaceVariantId' => data_get($installedVariant, 'id', null),
            'marketplaceSlug' => data_get($marketplaceData, 'slug', null),
            'version' => $package['version'], // Is this synchronized with git tag?
            'namespace' => $namespace,
            'directory' => $directory,
            'autoload' => $autoload,

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
