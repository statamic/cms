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

    public function display(): ?string
    {
        return $this->contents['display'] ?? null;
    }

    public function instructions(): ?string
    {
        return $this->contents['instructions'] ?? null;
    }

    public function collapsible(): ?bool
    {
        return $this->contents['collapsible'] ?? null;
    }

    public function collapsedByDefault(): ?bool
    {
        return $this->contents['collapsed_by_default'] ?? null;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function fields(): Fields
    {
        return new Fields(Arr::get($this->contents, 'fields', []));
    }

    public function toPublishArray(): array
    {
        return Arr::removeNullValues([
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'collapsible' => $this->collapsible(),
            'collapsed_by_default' => $this->collapsedByDefault(),
        ]) + [
            'fields' => $this->fields()->toPublishArray(),
        ];
    }
}
