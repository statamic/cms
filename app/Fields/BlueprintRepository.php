<?php

namespace Statamic\Fields;

use Statamic\API\YAML;
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
}
