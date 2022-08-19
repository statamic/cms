<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FieldsetRepository
{
    protected $fieldsets = [];
    protected $directory;
    protected $fieldsetDirectories = [];

    public function setDirectory($directory)
    {
        $this->directory = Path::tidy($directory);

        return $this;
    }

    public function directory()
    {
        return $this->directory;
    }

    public function find(string $handle): ?Fieldset
    {
        if ($cached = array_get($this->fieldsets, $handle)) {
            return $cached;
        }

        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        if (Str::contains($handle, '::')) {
            [$key, $fieldsetHandle] = explode('::', $handle);

            // check the "override" folder first
            if (File::exists($path = resource_path("fieldsets/vendor/{$key}/{$fieldsetHandle}.yaml"))) {
                return $this->addFieldset($fieldsetHandle, $path);
            }

            // then check addon folder
            if (! $directory = $this->getDirectory($key)) {
                return null;
            }

            return $this->addFieldset($handle, "{$directory}/{$fieldsetHandle}.yaml");
        }

        if (! File::exists($path = "{$this->directory}/{$path}.yaml")) {
            return null;
        }

        return $this->addFieldset($handle, $path);
    }

    public function exists(string $handle): bool
    {
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        if (Str::contains($handle, '::')) {
            [$key, $fieldsetHandle] = explode('::', $handle);

            if (! $directory = $this->getDirectory($key)) {
                return false;
            }

            return File::exists("{$directory}/{$fieldsetHandle}.yaml");
        }

        return File::exists("{$this->directory}/{$path}.yaml");
    }

    public function make($handle = null): Fieldset
    {
        return (new Fieldset)->setHandle($handle);
    }

    public function all(): Collection
    {
        $externalFieldsets = $this->fieldsetDirectories()
            ->flatMap(function (string $directory, string $key) {
                return $this->getFieldsetsByDirectory($directory, $key);
            });

        return $this
            ->getFieldsetsByDirectory($this->directory)
            ->merge($externalFieldsets);
    }

    public function save(Fieldset $fieldset)
    {
        if ($fieldset->isExternal()) {
            [$key, $handle] = explode('::', $fieldset->handle());
            $directory = resource_path("fieldsets/vendor/{$key}");
        } else {
            $handle = $fieldset->handle();
            $directory = $this->directory;
        }

        if (! File::exists($directory)) {
            File::makeDirectory($directory);
        }

        File::put(
            "{$directory}/{$handle}.yaml",
            YAML::dump($fieldset->contents())
        );
    }

    public function delete(Fieldset $fieldset)
    {
        File::delete("{$this->directory}/{$fieldset->handle()}.yaml");
    }

    public function addDirectory(string $directory, string $key): void
    {
        $this->fieldsetDirectories[$key] = $directory;
    }

    public function getDirectory(string $key): ?string
    {
        return Arr::get($this->fieldsetDirectories, $key);
    }

    public function fieldsetDirectories(): Collection
    {
        return collect($this->fieldsetDirectories);
    }

    private function getFieldsetsByDirectory(string $directory, string $key = null): Collection
    {
        return File::withAbsolutePaths()
            ->getFilesByTypeRecursively($directory, 'yaml')
            ->map(function ($file) use ($directory, $key) {
                $basename = str_after($file, str_finish($directory, '/'));
                $handle = str_before($basename, '.yaml');
                $handle = str_replace('/', '.', $handle);

                if ($key) {
                    $handle = "{$key}::{$handle}";
                }

                return $this
                    ->make($handle)
                    ->setContents(YAML::file($file)->parse());
            })
            ->keyBy->handle();
    }

    private function addFieldset(string $handle, string $path): Fieldset
    {
        return tap(new Fieldset, function (Fieldset $fieldset) use ($handle, $path) {
            $this->fieldsets[$handle] = $fieldset
                ->setHandle($handle)
                ->setContents(YAML::file($path)->parse());
        });
    }
}
