<?php

namespace Statamic\Fields;

use Statamic\API\YAML;
use Illuminate\Filesystem\Filesystem;

class FieldRepository
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

    public function find(string $field): ?Field
    {
        if (! str_contains($field, '.')) {
            return null;
        }

        list($fieldset, $handle) = explode('.', $field);

        if (! $fieldset = $this->fieldset($fieldset)) {
            return null;
        }

        if (! $config = array_get($fieldset['fields'], $handle)) {
            return null;
        }

        return new Field($handle, $config);
    }

    protected function fieldset(string $handle): ?array
    {
        if (! $this->files->exists($path = "{$this->directory}/{$handle}.yaml")) {
            return null;
        }

        return YAML::parse($this->files->get($path));
    }
}
