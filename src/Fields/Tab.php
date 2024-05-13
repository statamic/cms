<?php

namespace Statamic\Fields;

use Statamic\Support\Arr;
use Statamic\Support\Str;

class Tab
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
        $sections = Arr::get($this->contents, 'sections');

        // Handle situation where there's only fields defined, and not nested under sections.
        // Temporary?
        if (! $sections) {
            $sections = [
                [
                    'fields' => Arr::get($this->contents, 'fields', []),
                ],
            ];
        }

        $fields = collect($sections)->reduce(function ($carry, $section) {
            return $carry->merge(Arr::get($section, 'fields', []));
        }, collect())->all();

        return new Fields($fields);
    }

    public function sections()
    {
        $sections = Arr::get($this->contents, 'sections');

        // Handle situation where there's only fields defined, and not nested under sections.
        // Temporary?
        if (! $sections) {
            $sections = [
                [
                    'fields' => Arr::get($this->contents, 'fields', []),
                ],
            ];
        }

        return collect($sections)
            ->map(function ($section) {
                return new Section($section);
            });
    }

    public function toPublishArray()
    {
        return [
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'handle' => $this->handle,
            'sections' => $this->sections()->map->toPublishArray()->all(),
        ];
    }

    public function display()
    {
        return Arr::get($this->contents, 'display', __(Str::humanize($this->handle)));
    }

    public function instructions()
    {
        return Arr::get($this->contents, 'instructions');
    }
}
