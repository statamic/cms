<?php

namespace Statamic\Fields;

use Statamic\API\Str;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\BlueprintRepository;

class Blueprint
{
    protected $handle;
    protected $contents = [];
    protected $extraFields = [];

    public function setHandle(string $handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    public function setContents(array $contents)
    {
        if ($fields = array_pull($contents, 'fields')) {
            $contents['sections'] = [
                'main' => ['fields' => $fields]
            ];
        }

        $this->contents = $contents;

        return $this;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function sections(): Collection
    {
        $sections = array_get($this->contents, 'sections', []);
        $extra = $this->extraFields ?? [];

        $sections = collect($sections)->map(function ($contents, $handle) use (&$extra) {
            return (new Section($handle))
                ->setContents($contents)
                ->extraFields(array_pull($extra, $handle) ?? []);
        });

        foreach ($extra as $section => $fields) {
            $sections->put($section, (new Section($section))->extraFields($fields));
        }

        return $sections;
    }

    public function fields(): Fields
    {
        return $this->sections()->map->fields()->reduce(function ($carry, $fields) {
            return $carry->merge($fields);
        }, new Fields);
    }

    public function hasField($field)
    {
        return $this->fields()->has($field);
    }

    public function field($field)
    {
        return $this->fields()->get($field);
    }

    public function columns()
    {
        return new Columns($this->fields()->all()->map(function ($field) {
            return Column::make()
                ->field($field->handle())
                ->fieldtype($field->fieldtype()->indexComponent())
                ->label(__($field->display()))
                ->visibleDefault($field->isListable())
                ->visible($field->isListable())
                ->sortable($field->isSortable());
        }));
    }

    public function isEmpty(): bool
    {
        return $this->fields()->all()->isEmpty();
    }

    public function title()
    {
        return array_get($this->contents, 'title', Str::humanize($this->handle));
    }

    public function toPublishArray()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
            'sections' => $this->sections()->map->toPublishArray()->values()->all()
        ];
    }

    public function editUrl()
    {
        return cp_route('blueprints.edit', $this->handle());
    }

    public function save()
    {
        BlueprintRepository::save($this);

        return $this;
    }

    public function ensureField($handle, $field, $section = null, $prepend = false)
    {
        if ($this->hasField($handle)) {
            return $this;
        }

        // If a section hasn't been provided we'll just use the first section.
        if (! $section) {
            $section = array_keys($this->contents['sections'] ?? [])[0] ?? 'main';
        }

        $this->extraFields[$section][$handle] = compact('prepend', 'field');

        return $this;
    }

    public function ensureFieldPrepended($handle, $field, $section = null)
    {
        return $this->ensureField($handle, $field, $section, true);
    }
}
