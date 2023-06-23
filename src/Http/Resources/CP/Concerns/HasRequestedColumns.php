<?php

namespace Statamic\Http\Resources\CP\Concerns;

trait HasRequestedColumns
{
    protected function requestedColumns()
    {
        if (! $requested = $this->requestedColumnKeys()) {
            return $this->columns;
        }

        return $this->columns->keyBy('field')->only($requested)->values();
    }

    protected function visibleColumns()
    {
        if (! $requested = $this->requestedColumnKeys()) {
            return $this->columns;
        }

        $columns = $this->columns->keyBy('field')->map->visible(false);

        return collect($requested)
            ->filter(fn ($field) => $columns->has($field))
            ->map(fn ($field) => $columns->get($field)->visible(true))
            ->merge($columns->except($requested))
            ->values();
    }

    protected function requestedColumnKeys()
    {
        $columns = request('columns');

        if (! $columns) {
            return [];
        }

        return explode(',', $columns);
    }
}
