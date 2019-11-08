<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class Entry extends Resource
{
    protected $blueprint;
    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        $entry = $this->resource;
        $collection = $entry->collection();

        return [
            'id' => $entry->id(),
            'published' => $entry->published(),
            'date' => $this->when($collection->dated(), function () {
                return $this->resource->date()->inPreferredFormat();
            }),

            $this->merge($this->values(['slug' => $entry->slug()])),

            'edit_url' => $entry->editUrl(),
            'viewable' => User::current()->can('view', $entry),
            'editable' => User::current()->can('edit', $entry),
            'actions' => Action::for($entry, ['collection' => $collection->handle()]),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;

            $value = $this->blueprint
                ->field($key)
                ->setValue($extra[$key] ?? $this->resource->value($key))
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
