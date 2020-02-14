<?php

namespace Statamic\Fields;

use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\BlueprintRepository;

class Blueprint
{
    protected $handle;
    protected $contents = [];
    protected $extraFields = [];
    protected $fieldsCache;

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

        return $this->resetFieldsCache();
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
        if ($this->fieldsCache) {
            return $this->fieldsCache;
        }

        $this->validateUniqueHandles();

        $fields = new Fields($this->sections()->map->fields()->flatMap->items());

        $this->fieldsCache = $fields;

        return $fields;
    }

    public function hasField($field)
    {
        return $this->fields()->has($field);
    }

    public function hasFieldInSection($field, $section)
    {
        if ($section = $this->sections()->get($section)) {
            return $section->fields()->has($field);
        }

        return false;
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
                ->listable($field->isListable())
                ->visibleDefault($field->isVisible())
                ->visible($field->isVisible())
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
            'sections' => $this->sections()->map->toPublishArray()->values()->all(),
            'empty' => $this->isEmpty(),
        ];
    }

    public function editUrl()
    {
        return cp_route('blueprints.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('blueprints.destroy', $this->handle());
    }

    public function save()
    {
        BlueprintRepository::save($this);

        return $this;
    }

    public function delete()
    {
        BlueprintRepository::delete($this);

        return true;
    }

    public function ensureField($handle, $field, $section = null, $prepend = false)
    {
        if ($this->hasField($handle)) {
            // Loop through all sections looking for the handle so we can ensure the required config
            foreach($this->contents['sections'] ?? [] as $section_key => $blueprint_section) {
                foreach ($blueprint_section['fields'] as $field_key => $blueprint_field) {
                    if (array_get($blueprint_field, 'handle') == $handle) {
                        $this->contents['sections'][$section_key]['fields'][$field_key]['field'] = array_merge(
                            $field,
                            $this->contents['sections'][$section_key]['fields'][$field_key]['field']
                        );

                        return $this->resetFieldsCache();
                    }
                }
            }
        }

        // If a section hasn't been provided we'll just use the first section.
        if (! $section) {
            $section = array_keys($this->contents['sections'] ?? [])[0] ?? 'main';
        }

        $this->extraFields[$section][$handle] = compact('prepend', 'field');

        return $this->resetFieldsCache();
    }

    public function ensureFieldPrepended($handle, $field, $section = null)
    {
        return $this->ensureField($handle, $field, $section, true);
    }

    protected function validateUniqueHandles()
    {
        $handles = $this->sections()->map->contents()->flatMap(function ($contents) {
            return array_get($contents, 'fields', []);
        })->map(function ($item) {
            return $item['handle'] ?? null;
        })->filter();

        if ($field = $handles->duplicates()->first()) {
            throw new \Exception("Duplicate field [{$field}] on blueprint [{$this->handle}].");
        }
    }

    protected function resetFieldsCache()
    {
        $this->fieldsCache = null;

        return $this;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Blueprint::{$method}(...$parameters);
    }
}
