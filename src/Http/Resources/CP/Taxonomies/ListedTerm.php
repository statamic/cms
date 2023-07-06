<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedTerm extends JsonResource
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
        $term = $this->resource;
        $taxonomy = $term->taxonomy();

        return [
            'id' => $term->id(),
            'published' => $term->published(),
            'private' => $term->private(),

            $this->merge($this->values([
                'title' => $term->title(),
                'slug' => $term->slug(),
            ])),

            'permalink' => $term->absoluteUrl(),
            'edit_url' => $term->editUrl(),
            'taxonomy' => $term->taxonomy()->toArray(),
            'viewable' => User::current()->can('view', $term),
            'editable' => User::current()->can('edit', $term),
            'actions' => Action::for($term, ['taxonomy' => $taxonomy->handle()]),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;

            if ($key == 'taxonomy') {
                return [$key => $this->resource->taxonomy()->title()];
            }

            $value = $this->blueprint
                ->field($key)
                ->setValue($extra[$key] ?? $this->resource->value($key))
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
