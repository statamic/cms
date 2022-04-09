<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;

class FieldsetRepository
{
    protected $fieldsets = [];
    protected $directories = [];

    public function setDirectory($directories)
    {
        if (is_string($directories)) {
            $directories = [$directories];
        }

        $this->directories = array_map(fn($directory) => Path::tidy($directory), $directories);

        return $this;
    }

    public function directory()
    {
        return array_first($this->directories);
    }

    public function directories()
    {
        return $this->directories;
    }

    public function path(string $handle): ?string
    {
        $path = str_replace('.', '/', $handle);

        foreach($this->directories as $directory) {
            if (File::exists("{$directory}/{$path}.yaml")) {
                return Path::tidy(vsprintf('%s/%s.yaml', [
                    $directory,
                    $path
                ]));
            }
        }

        return null;
    }

    public function find(string $handle): ?Fieldset
    {
        if ($cached = array_get($this->fieldsets, $handle)) {
            return $cached;
        }

        $handle = str_replace('/', '.', $handle);
        $path = $this->path($handle);

        if ($path === null) {
            return null;
        }

        $fieldset = (new Fieldset)
            ->setHandle($handle)
            ->setContents(YAML::file($path)->parse());

        $this->fieldsets[$handle] = $fieldset;

        return $fieldset;
    }

    public function exists(string $handle): bool
    {
        $handle = str_replace('/', '.', $handle);
        $filename = str_replace('.', '/', $handle);

        return $this->path($handle) !== null;
    }

    public function make($handle = null): Fieldset
    {
        return (new Fieldset)->setHandle($handle);
    }

    public function all(): Collection
    {
        $fieldsets = collect();

        foreach($this->directories as $directory) {
            if (! File::exists($directory)) {
                continue;
            }

            File::withAbsolutePaths()
                ->getFilesByTypeRecursively($directory, 'yaml')
                ->map(function ($file) use ($directory) {
                    $basename = str_after($file, str_finish($directory, '/'));
                    $handle = str_before($basename, '.yaml');
                    $handle = str_replace('/', '.', $handle);

                    return (new Fieldset)
                        ->setHandle($handle)
                        ->setContents(YAML::file($file)->parse());
                })
                ->keyBy->handle()
                ->each(function($fieldset, $handle) use ($fieldsets) {
                    $fieldsets->put($handle, $fieldset);
                });
        }

        return $fieldsets;
    }

    public function save(Fieldset $fieldset)
    {
        $handle = str_replace('/', '.', $fieldset->handle());
        $path = $this->path($handle);

        if ($path === null) {
            $path = sprintf("%s/%s.yaml", $this->directory(), str_replace('.', '/', $handle));
        }

        if (! File::exists(dirname($path))) {
            File::makeDirectory(dirname($path));
        }

        File::put(
            $path,
            YAML::dump($fieldset->contents())
        );
    }

    public function delete(Fieldset $fieldset)
    {
        $handle = str_replace('/', '.', $fieldset->handle());

        if (($path = $this->path($handle)) !== null) {
            File::delete($path);
        }
    }
}
