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
            ->initialPath($path)
            ->setContents(YAML::file($path)->parse());

        $this->fieldsets[$handle] = $fieldset;

        return $fieldset;
    }

    private function standardFieldsetPath(string $handle)
    {
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        if (Str::startsWith($handle, 'vendor.')) {
            return null;
        }

        return "{$this->directory}/{$path}.yaml";
    }

    protected function findStandardFieldsetPath(string $handle)
    {
        if (! $path = $this->standardFieldsetPath($handle)) {
            return null;
        }

        if (File::exists($path)) {
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
        return $this->getStandardFieldsets()->merge($this->getNamespacedFieldsets());
    }

    protected function getStandardFieldsets()
    {
        return $this->getFieldsetsByDirectory($this->directory);
    }

    protected function getNamespacedFieldsets()
    {
        return collect($this->hints)
            ->flatMap(function (string $directory, string $namespace) {
                return $this->getFieldsetsByDirectory($directory, $namespace);
            });
    }

    public function save(Fieldset $fieldset)
    {
        $directory = $this->directory;

        if ($fieldset->isNamespaced()) {
            [$key, $handle] = explode('::', $fieldset->handle());
            $directory = $this->directory.'/vendor/'.$key;
        }

        $handle = Str::of($fieldset->handle())->after('::')->replace('.', '/');

        File::put(
            "{$directory}/{$handle}.yaml",
            YAML::dump($fieldset->contents())
        );
    }

    public function delete(Fieldset $fieldset)
    {
        if ($fieldset->isNamespaced()) {
            throw new \Exception('Namespaced fieldsets cannot be deleted');
        }

        File::delete("{$this->directory}/{$fieldset->handle()}.yaml");
    }

    public function addNamespace(string $namespace, string $directory): void
    {
        $this->hints[$namespace] = $directory;
    }

    protected function getFieldsetsByDirectory(string $directory, string $namespace = null): Collection
    {
        return File::withAbsolutePaths()
            ->getFilesByTypeRecursively($directory, 'yaml')
            ->reject(fn ($path) => Str::startsWith($path, $directory.'/vendor/'))
            ->map(function ($file) use ($directory, $namespace) {
                $basename = str_after($file, str_finish($directory, '/'));
                $handle = str_before($basename, '.yaml');
                $handle = str_replace('/', '.', $handle);

                if ($namespace) {
                    $handle = "{$namespace}::{$handle}";

                    if (File::exists($override = $this->overriddenNamespacedFieldsetPath($handle))) {
                        $file = $override;
                    }
                }

                return $this
                    ->make($handle)
                    ->initialPath($file)
                    ->setContents(YAML::file($file)->parse());
            })
            ->keyBy->handle();
    }
}
