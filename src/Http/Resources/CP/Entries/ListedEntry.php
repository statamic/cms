<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedEntry extends JsonResource
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
            'private' => $entry->private(),
            'date' => $this->when($collection->dated(), function () {
                return $this->resource->date()->inPreferredFormat();
            }),

            $this->merge($this->values(['slug' => $entry->slug()])),

            'permalink' => $entry->absoluteUrl(),
            'edit_url' => $entry->editUrl(),
            'collection' => $entry->collection()->toArray(),
            'viewable' => User::current()->can('view', $entry),
            'editable' => User::current()->can('edit', $entry),
            'actions' => Action::for($entry, ['collection' => $collection->handle()]),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;

            if ($key === 'site') {
                $value = $this->resource->locale();
            } else {
                $value = $extra[$key] ?? $this->resource->value($key);
            }

            $value = $this->blueprint
                ->field($key)
                ->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
