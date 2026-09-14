<?php

namespace Statamic\Http\Resources\API;

trait ResolvesRequestedFields
{
    protected function requestedFields($request): ?array
    {
        if ($selected = $this->resource->selectedQueryColumns()) {
            return $selected;
        }

        $fields = $request->input('fields');

        if (! is_string($fields) || $fields === '*') {
            return null;
        }

        return collect(explode(',', $fields))
            ->map(fn ($field) => trim($field))
            ->filter()
            ->values()
            ->all();
    }
}
