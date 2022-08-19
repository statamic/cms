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
    protected $hints = [];

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

        $path = Str::contains($handle, '::')
            ? $this->findNamespacedFieldsetPath($handle)
            : $this->findStandardFieldsetPath($handle);

        if (! $path) {
            return null;
        }

        $fieldset = $this
            ->make($handle)
            ->setContents(YAML::file($path)->parse());

        $this->fieldsets[$handle] = $fieldset;

        return $fieldset;
    }

    private function standardFieldsetPath(string $handle)
    {
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        return "{$this->directory}/{$path}.yaml";
    }

    private function findStandardFieldsetPath(string $handle)
    {
        if (File::exists($path = $this->standardFieldsetPath($handle))) {
            return $path;
        }
    }

    private function findNamespacedFieldsetPath(string $handle)
    {
        $paths = [
            $this->overriddenNamespacedFieldsetPath($handle),
            $this->namespacedFieldsetPath($handle),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }
    }

    private function namespacedFieldsetPath(string $handle)
    {
        [$namespace, $handle] = explode('::', $handle);

        if (! isset($this->hints[$namespace])) {
            return null;
        }

        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        return "{$this->hints[$namespace]}/{$path}.yaml";
    }

    private function overriddenNamespacedFieldsetPath(string $handle)
    {
        [$namespace, $handle] = explode('::', $handle);
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        return "{$this->directory}/vendor/{$namespace}/{$path}.yaml";
    }

    public function exists(string $handle): bool
    {
        $path = Str::contains($handle, '::')
            ? $this->namespacedFieldsetPath($handle)
            : $this->standardFieldsetPath($handle);

        return $path ? File::exists($path) : false;
    }

    public function make($handle = null): Fieldset
    {
        return (new Fieldset)->setHandle($handle);
    }

    public function all(): Collection
    {
        $namespaced = collect($this->hints)
            ->flatMap(function (string $directory, string $key) {
                return $this->getFieldsetsByDirectory($directory, $key);
            });

        return $this
            ->getFieldsetsByDirectory($this->directory)
            ->merge($namespaced);
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

    public function addNamespace(string $namespace, string $directory): void
    {
        $this->hints[$namespace] = $directory;
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
}
