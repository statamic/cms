<?php

namespace Statamic\Fields;

use Statamic\API\YAML;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class FieldsetRepository
{
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
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        if (! $this->files->exists($path = "{$this->directory}/{$path}.yaml")) {
            return null;
        }

        return (new Fieldset)
            ->setHandle($handle)
            ->setContents(YAML::parse($this->files->get($path)));
    }

    public function all(): Collection
    {
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
        $this->files->put(
            "{$this->directory}/{$fieldset->handle()}.yaml",
            YAML::dump($fieldset->contents())
        );
    }
}
