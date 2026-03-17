<?php

namespace Statamic\Addons;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Addons\SettingsRepository as AbstractSettingsRepository;
use Statamic\Contracts\Addons\Settings as AddonSettingsContract;
use Statamic\Facades;
use Statamic\Facades\YAML;

class FileSettingsRepository extends AbstractSettingsRepository
{
    public function find(string $addon): ?AddonSettingsContract
    {
        $slug = Str::after($addon, '/');

        $path = resource_path("addons/{$slug}.yaml");

        if (! File::exists($path)) {
            return null;
        }

        return $this->makeFromPath($path);
    }

    public function save(AddonSettingsContract $settings): bool
    {
        File::ensureDirectoryExists(resource_path('addons'));

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

        $addon = Facades\Addon::all()->first(fn ($addon) => $addon->slug() === basename($path, '.yaml'));

        return $this->make($addon, $yaml);
    }

    public static function bindings(): array
    {
        return [
            AddonSettingsContract::class => FileSettings::class,
        ];
    }
}
