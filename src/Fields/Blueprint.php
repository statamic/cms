<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Blueprint implements Augmentable
{
    use HasAugmentedData;

    protected $handle;
    protected $contents = [];
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
        return collect(Arr::get($this->contents, 'sections', []))->map(function ($contents, $handle) {
            return (new Section($handle))->setContents($contents);
        });
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
        $columns = $this->fields()
            ->all()
            ->values()
            ->map(function ($field, $index) {
                return Column::make()
                    ->field($field->handle())
                    ->fieldtype($field->fieldtype()->indexComponent())
                    ->label(__($field->display()))
                    ->listable($field->isListable())
                    ->defaultVisibility($field->isVisible())
                    ->visible($field->isVisible())
                    ->sortable($field->isSortable())
                    ->defaultOrder($index + 1);
            })
            ->keyBy('field');

        return new Columns($columns);
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
        return $this->handle() ? cp_route('blueprints.edit', $this->handle()) : null;
    }

    public function deleteUrl()
    {
        return $this->handle() ? cp_route('blueprints.destroy', $this->handle()) : null;
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

    public function ensureField($handle, $fieldConfig, $section = null, $prepend = false)
    {
        // If blueprint already has field, merge it's config in the field's current section.
        if ($this->hasField($handle)) {
            foreach ($this->sections()->keys() as $sectionKey) {
                if ($this->hasFieldInSection($handle, $sectionKey)) {
                    return $this->ensureFieldInSection($handle, $fieldConfig, $sectionKey);
                }
            }
        }

        // If a section hasn't been provided we'll just use the first section, or default.
        $section = $section ?? $this->sections()->keys()->first() ?? 'main';

        return $this->ensureFieldInSection($handle, $fieldConfig, $section, $prepend);
    }

    public function ensureFieldInSection($handle, $fieldConfig, $section, $prepend = false)
    {
        // Ensure section exists.
        if (! isset($this->contents['sections'][$section])) {
            $this->contents['sections'][$section] = [];
        }

        // Get fields from section, including imported fields.
        $fields = $this->sections()->get($section)->fields()->all()->map(function ($field, $handle) {
            return [
                'handle' => $handle,
                'field' => $field->config(),
            ];
        });

        // If it already exists, merge field config.
        if ($exists = $this->hasFieldInSection($handle, $section)) {
            $fieldConfig = array_merge($fieldConfig, $fields[$handle]['field']);
        }

        // Combine handle and field config.
        $field = [
            'handle' => $handle,
            'field' => $fieldConfig,
        ];

        // Set the field config in it's proper place.
        if ($prepend && $exists) {
            $fields->forget($handle)->prepend($field);
        } elseif ($prepend && ! $exists) {
            $fields->prepend($field);
        } elseif ($exists) {
            $fields->put($handle, $field);
        } else {
            $fields->push($field);
        }

        // Set fields back into blueprint contents.
        $this->contents['sections'][$section]['fields'] = $fields->values()->all();

        return $this->resetFieldsCache();
    }

    public function ensureFieldPrepended($handle, $field, $section = null)
    {
        return $this->ensureField($handle, $field, $section, true);
    }

    public function removeField($handle, $section = null)
    {
        if (! $this->hasField($handle)) {
            return $this;
        }

        // If a section is specified, only remove from that specific section.
        if ($section) {
            return $this->removeFieldFromSection($handle, $section);
        }

        // Otherwise remove from any section.
        foreach ($this->sections()->keys() as $sectionKey) {
            if ($this->hasFieldInSection($handle, $sectionKey)) {
                return $this->removeFieldFromSection($handle, $sectionKey);
            }
        }
    }

    public function removeFieldFromSection($handle, $section)
    {
        $fields = collect($this->contents['sections'][$section]['fields'] ?? []);

        // See if field already exists in section.
        if ($this->hasFieldInSection($handle, $section)) {
            $fieldKey = $fields->search(function ($field) use ($handle) {
                return Arr::get($field, 'handle') === $handle;
            });
        } else {
            return $this;
        }

        // Pull it out.
        Arr::pull($this->contents['sections'][$section]['fields'], $fieldKey);

        return $this->resetFieldsCache();
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

    public function __toString()
    {
        return $this->handle();
    }

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['handle', 'title'];
    }
}
