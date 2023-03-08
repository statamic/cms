<?php

namespace Statamic\Fields;

use Statamic\Support\Arr;

class Section
{
    protected $contents;

    public function __construct($contents)
    {
        $this->contents = $contents;
    }

    public function display()
    {
        return $this->contents['display'] ?? null;
    }

    public function instructions()
    {
        return $this->contents['instructions'] ?? null;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function toPublishArray()
    {
        return Arr::removeNullValues([
            'display' => $this->contents['display'] ?? null,
            'instructions' => $this->contents['instructions'] ?? null,
            'fields' => (new Fields(Arr::get($this->contents, 'fields', [])))->toPublishArray(),
        ]);
    }
}
