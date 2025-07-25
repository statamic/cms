<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Support\Str;

class FolderAsset extends JsonResource
{
    use HasThumbnails;

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
        return [
            'id' => $this->id(),
            'basename' => $this->basename(),
            'extension' => $this->extension(),
            'url' => $this->absoluteUrl(),
            'size_formatted' => Str::fileSizeForHumans($this->size(), 0),
            'last_modified_relative' => $this->lastModified()->diffForHumans(),

            $this->merge($this->values()),

            'actions' => Action::for($this->resource, [
                'container' => $this->container()->handle(),
                'folder' => $this->folder(),
            ]),

            $this->merge($this->thumbnails()),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            $value = $extra[$key] ?? $this->resource->get($key) ?? $field?->defaultValue();

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
