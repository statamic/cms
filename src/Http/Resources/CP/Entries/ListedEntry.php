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
            'status' => $entry->status(),
            'private' => $entry->private(),
            'date' => $this->when($collection->dated(), function () {
                return $this->resource->blueprint()->field('date')->fieldtype()->preProcessIndex($this->resource->date());
            }),

            $this->merge($this->values(['slug' => $entry->slug()])),

            'permalink' => $entry->absoluteUrl(),
            'edit_url' => $entry->editUrl(),
            'collection' => array_merge($entry->collection()->toArray(), ['dated' => $entry->collection()->dated()]),
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

            $field = $this->blueprint->field($key);

            if (! $field) {
                return [$key => $value];
            }

            $value = $field->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
