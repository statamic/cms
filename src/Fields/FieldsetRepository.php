<?php

namespace Statamic\Fields;

use Statamic\Facades\YAML;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class FieldsetRepository
{
    protected $fieldsets = [];
    protected $files;
    protected $directory;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    public function find(string $handle): ?Fieldset
    {
        if ($cached = array_get($this->fieldsets, $handle)) {
            return $cached;
        }

        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        if (! $this->files->exists($path = "{$this->directory}/{$path}.yaml")) {
            return null;
        }

        $fieldset = (new Fieldset)
            ->setHandle($handle)
            ->setContents(YAML::parse($this->files->get($path)));

        $this->fieldsets[$handle] = $fieldset;

        return $fieldset;
    }

    public function exists(string $handle): bool
    {
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        return $this->files->exists($path = "{$this->directory}/{$path}.yaml");
    }

    public function make($handle = null): Fieldset
    {
        return (new Fieldset)->setHandle($handle);
    }

    public function all(): Collection
    {
        if (! $this->files->exists($this->directory)) {
            return collect();
        }

        return collect($this->files->allFiles($this->directory))
            ->filter(function ($file) {
                return $file->getExtension() === 'yaml';
            })
            ->map(function ($file) {
                $basename = str_after($file->getPathname(), str_finish($this->directory, '/'));
                $handle = str_before($basename, '.yaml');
                $handle = str_replace('/', '.', $handle);

                return (new Fieldset)
                    ->setHandle($handle)
                    ->setContents(YAML::parse($this->files->get($file->getPathname())));
            })
            ->keyBy->handle();
    }

    public function save(Fieldset $fieldset)
    {
        if (! $this->files->exists($this->directory)) {
            $this->files->makeDirectory($this->directory);
        }

        $this->files->put(
            "{$this->directory}/{$fieldset->handle()}.yaml",
            YAML::dump($fieldset->contents())
        );
    }

    public function delete(Fieldset $fieldset)
    {
        $this->files->delete("{$this->directory}/{$fieldset->handle()}.yaml");
    }
}
