<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\API\Field as FieldAPI;
use Statamic\API\Str;

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
        return new Fields(array_get($this->contents, 'fields', []));
    }

    public function toPublishArray()
    {
        return [
            'display' => $this->display(),
            'handle' => $this->handle,
            'fields' => $this->fields()->toPublishArray()
        ];
    }

    public function display()
    {
        return array_get($this->contents, 'display', Str::humanize($this->handle));
    }
}
