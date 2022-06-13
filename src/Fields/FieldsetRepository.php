<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;

class FieldsetRepository
{
    protected $fieldsets = [];
    protected $directory;

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

        if (! File::exists($path = "{$this->directory}/{$path}.yaml")) {
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
        $path = str_replace('.', '/', $handle);

        return File::exists($path = "{$this->directory}/{$path}.yaml");
    }

    public function make($handle = null): Fieldset
    {
        return (new Fieldset)->setHandle($handle);
    }

    public function all(): Collection
    {
        if (! File::exists($this->directory)) {
            return collect();
        }

        return File::withAbsolutePaths()
            ->getFilesByTypeRecursively($this->directory, 'yaml')
            ->map(function ($file) {
                $basename = str_after($file, str_finish($this->directory, '/'));
                $handle = str_before($basename, '.yaml');
                $handle = str_replace('/', '.', $handle);

                return (new Fieldset)
                    ->setHandle($handle)
                    ->setContents(YAML::file($file)->parse());
            })
            ->keyBy->handle();
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
}
