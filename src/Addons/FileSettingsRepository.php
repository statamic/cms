<?php

namespace Statamic\Addons;

use Illuminate\Support\Facades\File;
use Statamic\Addons\SettingsRepository as AbstractSettingsRepository;
use Statamic\Contracts\Addons\Settings as AddonSettingsContract;
use Statamic\Facades;
use Statamic\Facades\YAML;

class FileSettingsRepository extends AbstractSettingsRepository
{
    public function find(string $addon): ?AddonSettingsContract
    {
        if (! $addon = Facades\Addon::get($addon)) {
            return null;
        }

        $path = resource_path("addons/{$addon->slug()}.yaml");

        if (! File::exists($path)) {
            return null;
        }

        return $this->make($addon, YAML::file($path)->parse());
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

    public static function bindings(): array
    {
        return [
            AddonSettingsContract::class => FileSettings::class,
        ];
    }
}
