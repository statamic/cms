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

    public function toPublishArray()
    {
        return [
            'display' => $this->contents['display'] ?? null,
            'instructions' => $this->contents['instructions'] ?? null,
            'fields' => (new Fields(Arr::get($this->contents, 'fields', [])))->toPublishArray(),
        ];
    }
}
