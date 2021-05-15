<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class FieldsetRepository
{
    protected $fieldsets = [];
    protected $directory;
    protected $addonDirectories = [];

    public function setDirectory($directory)
    {
        $this->directory = Path::tidy($directory);

        return $this;
    }

    public function find(string $handle): ?Fieldset
    {
        if ($cached = array_get($this->fieldsets, $handle)) {
            return $cached;
        }

        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        $directory = $this->directories()->first(function ($directory) use ($path) {
            return File::exists("{$directory}/{$path}.yaml");
        });

        if (! $directory) {
            return null;
        }

        $fieldset = (new Fieldset)
            ->setHandle($handle)
            ->setIsAddonFieldset(Str::startsWith($directory, resource_path()))
            ->setContents(YAML::file("{$directory}/{$path}.yaml")->parse());

        $this->fieldsets[$handle] = $fieldset;

        return $fieldset;
    }

    public function exists(string $handle): bool
    {
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        return $this->directories()->contains(function ($directory) use ($path) {
            return File::exists("{$directory}/{$path}.yaml");
        });
    }

    public function make($handle = null): Fieldset
    {
        return (new Fieldset)->setHandle($handle);
    }

    public function all(): Collection
    {
        $fieldsets = $this->directories()
            ->flatMap(function (string $directory) {
                return $this->getFieldsetsByDirectory($directory);
            });

        if ($fieldsets->isEmpty()) {
            return collect();
        }

        return $fieldsets;
    }

    public function save(Fieldset $fieldset)
    {
        if (! File::exists($this->directory)) {
            File::makeDirectory($this->directory);
        }

        File::put(
            "{$this->directory}/{$fieldset->handle()}.yaml",
            YAML::dump($fieldset->contents())
        );
    }

    public function delete(Fieldset $fieldset)
    {
        File::delete("{$this->directory}/{$fieldset->handle()}.yaml");
    }

    public function addDirectory(string $directory): void
    {
        $this->addonDirectories[] = $directory;
    }

    public function directories(): Collection
    {
        return collect($this->addonDirectories)->merge($this->directory);
    }

    private function getFieldsetsByDirectory(string $directory): Collection
    {
        return File::withAbsolutePaths()
            ->getFilesByTypeRecursively($directory, 'yaml')
            ->map(function ($file) use ($directory) {
                $basename = str_after($file, str_finish($directory, '/'));
                $handle = str_before($basename, '.yaml');
                $handle = str_replace('/', '.', $handle);

                return (new Fieldset)
                    ->setHandle($handle)
                    ->setIsAddonFieldset(! Str::startsWith($directory, resource_path()))
                    ->setContents(YAML::file($file)->parse());
            })
            ->keyBy->handle();
    }
}
