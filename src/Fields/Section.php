<?php

namespace Statamic\Fields;

use Statamic\Support\Arr;

class Section
{
    protected $contents;
    protected ?Fields $fields;

    public function __construct($contents, ?Fields $fields = null)
    {
        $this->contents = $contents;
        $this->fields = $fields;
    }

    public function display(): ?string
    {
        return $this->contents['display'] ?? null;
    }

    public function instructions(): ?string
    {
        return $this->contents['instructions'] ?? null;
    }

    public function collapsible(): bool
    {
        return $this->contents['collapsible'] ?? false;
    }

    public function collapsed(): bool
    {
        return $this->collapsible() && ($this->contents['collapsed'] ?? false);
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function fields(): Fields
    {
        return $this->fields ?? new Fields(Arr::get($this->contents, 'fields', []));
    }

    public function toPublishArray(): array
    {
        return Arr::removeNullValues([
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'collapsible' => $this->collapsible() ?: null,
            'collapsed' => $this->collapsed() ?: null,
        ]) + [
            'fields' => $this->fieldsToPublishArray(),
        ];
    }

    private function fieldsToPublishArray(): array
    {
        // Fields::toPublishArray() caches by handle and config, which would
        // conflate injected fields that only differ by their prefix.
        if ($this->fields) {
            return $this->fields->all()->map->toPublishArray()->values()->all();
        }

        return $this->fields()->toPublishArray();
    }
}
