<?php

namespace Statamic\Fields;

use Statamic\API\YAML;
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
        if (! $this->files->exists($path = "{$this->directory}/{$handle}.yaml")) {
            return null;
        }

        return (new Fieldset)
            ->setHandle($handle)
            ->setContents(YAML::parse($this->files->get($path)));
    }
}
