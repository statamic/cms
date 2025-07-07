<?php

namespace Statamic\Extend;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Contracts\Extend\AddonSettings as AddonSettingsContract;
use Statamic\Contracts\Extend\AddonSettingsRepository as Contract;
use Statamic\Facades;
use Statamic\Facades\YAML;

class AddonSettingsRepository implements Contract
{
    public function make(Addon $addon, array $settings = []): AddonSettingsContract
    {
        return app()->makeWith(AddonSettingsContract::class, compact('addon', 'settings'));
    }

    public function find(string $addon): ?AddonSettingsContract
    {
        $path = storage_path('statamic/addons/'.$addon.'.yaml');

        if (! File::exists($path)) {
            return null;
        }

        return $this->makeFromPath($path);
    }

    public function save(AddonSettingsContract $settings): bool
    {
        File::ensureDirectoryExists(dirname($settings->path()));

        File::put($settings->path(), $settings->fileContents());

        return true;
    }

    public function delete(AddonSettingsContract $settings): bool
    {
        File::delete($settings->path());

        return true;
    }

    private function makeFromPath(string $path): AddonSettingsContract
    {
        $yaml = YAML::file($path)->parse();

        $vendor = Str::of($path)->beforeLast('/')->afterLast('/')->__toString();
        $package = Str::of($path)->afterLast('/')->before('.yaml')->__toString();

        $addon = Facades\Addon::get("{$vendor}/{$package}");

        return $this->make($addon, $yaml);
    }

    public static function bindings(): array
    {
        return [
            AddonSettingsContract::class => AddonSettings::class,
        ];
    }
}
