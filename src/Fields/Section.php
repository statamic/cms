<?php

namespace Statamic\Fields;

use Statamic\Support\Arr;
use Statamic\Support\Str;

class Section
{
    protected $handle;
    protected $contents = [];

    public function __construct($handle)
    {
        $this->handle = $handle;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    public function setContents(array $contents)
    {
        $this->contents = $contents;

        return $this;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function fields(): Fields
    {
        return new Fields(Arr::get($this->contents, 'fields', []));
    }

    public function toPublishArray()
    {
        return [
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'handle' => $this->handle,
            'fields' => $this->fields()->toPublishArray(),
        ];
    }

    public function display()
    {
        return array_get($this->contents, 'display', __(Str::humanize($this->handle)));
    }

    public function instructions()
    {
        return array_get($this->contents, 'instructions');
    }
}
