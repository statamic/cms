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
            ->map(function ($field) use ($columns) {
                if ($columns->get($field) !== null) {
                    return $columns->get($field)->visible(true);
                }

                return false;
            })
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
