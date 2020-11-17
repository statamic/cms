<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Entries extends ResourceCollection
{
    public $collects = ListedEntry::class;
    protected $blueprint;
    protected $columnPreferenceKey;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columnPreferenceKey($key)
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    private function setColumns()
    {
        $columns = $this->blueprint->columns();

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    private function requestedColumns()
    {
        if (! $requested = $this->requestedColumnKeys()) {
            return $this->columns;
        }

        return $this->columns->keyBy('field')->only($requested)->values();
    }

    private function visibleColumns()
    {
        if (! $requested = $this->requestedColumnKeys()) {
            return $this->columns;
        }

        $columns = $this->columns->keyBy('field')->map->visible(false);

        return collect($requested)
            ->map(function ($field) use ($columns) {
                return $columns->get($field)->visible(true);
            })
            ->merge($columns->except($requested))
            ->values();
    }

    private function requestedColumnKeys()
    {
        $columns = request('columns');

        if (! $columns) {
            return [];
        }

        return explode(',', $columns);
    }

    public function toArray($request)
    {
        $this->setColumns();

        return [
            'data' => $this->collection->each(function ($entry) {
                $entry
                    ->blueprint($this->blueprint)
                    ->columns($this->requestedColumns());
            }),

            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
