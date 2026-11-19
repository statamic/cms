<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use ArrayObject;
use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\User;

class ListedTerm extends JsonResource
{
    protected $blueprint;
    protected $columns;
    protected ?ArrayObject $taxonomies = null;

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

    /**
     * A place to keep each taxonomy's array, shared by every term in a listing.
     */
    public function taxonomies(ArrayObject $taxonomies)
    {
        $this->taxonomies = $taxonomies;

        return $this;
    }

    public function toArray($request)
    {
        $term = $this->resource;

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
            'has_template' => view()->exists($term->template()),
            'taxonomy' => $this->taxonomyArray($term->taxonomy()),
            'viewable' => User::current()->can('view', $term),
            'editable' => User::current()->can('edit', $term),
        ];
    }

    protected function taxonomyArray($taxonomy)
    {
        // Every term in a listing would otherwise augment its taxonomy again, URLs and all.
        if (! $this->taxonomies) {
            return $taxonomy->toArray();
        }

        return $this->taxonomies[$taxonomy->handle()] ??= $taxonomy->toArray();
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            if ($key == 'type') {
                return [$key => $this->resource->taxonomy()->title()];
            } else {
                $value = $extra[$key] ?? $this->resource->value($key) ?? $field?->defaultValue();
            }

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
