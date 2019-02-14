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

        return collect($sections)->map(function ($contents, $handle) {
            return (new Section($handle))->setContents($contents);
        });
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

    public function columns($listable = null)
    {
        $fields = $this->fields()->all();

        return new Columns($this->sortListableFieldsFirst($listable, $fields)->map(function ($field) use ($listable) {
            return Column::make()
                ->field($field->handle())
                ->fieldtype($field->fieldtype()->handle())
                ->label(__($field->display()))
                ->visible(is_array($listable) ? in_array($field->handle(), $listable) : $field->isListable());
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

    protected function sortListableFieldsFirst($listable, $fields)
    {
        return $fields
            ->values()
            ->keyBy(function ($field, $key) use ($listable) {
                $listableKey = array_search($field->handle(), $listable ?? []);
                return $listableKey !== false ? '_' . $listableKey : $key + 1;
            })
            ->sortKeys()
            ->keyBy->handle();
    }
}
