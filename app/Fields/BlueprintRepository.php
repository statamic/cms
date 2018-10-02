<?php

namespace Statamic\Fields;

use Statamic\API\YAML;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class BlueprintRepository
{
    protected $files;
    protected $directory;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function setDirectory(string $directory)
    {
        $this->directory = $directory;

        return $this;
    }

    public function find($handle): ?Blueprint
    {
        if (! $this->files->exists($path = "{$this->directory}/{$handle}.yaml")) {
            return null;
        }

        return (new Blueprint)
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

                return (new Blueprint)
                    ->setHandle($handle)
                    ->setContents(YAML::parse($this->files->get($file->getPathname())));
            })
            ->keyBy->handle();
    }
}
