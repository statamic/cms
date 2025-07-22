<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Fluent;
use Statamic\Facades\Action;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;

class FolderAsset extends JsonResource
{
    use Hookable;

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

    private function thumbnails(): array
    {
        $data = match (true) {
            $this->isImage() || $this->isSvg() => $this->getImageThumbnail(),
            $this->isVideo() && config('statamic.assets.video_thumbnails', true) => $this->getVideoThumbnail(),
            default => ['thumbnail' => null],
        };

        return array_merge($data, $this->runAssetHook() ?? []);
    }

    private function getImageThumbnail(): array
    {
        return [
            'is_image' => true,
            'thumbnail' => $this->thumbnailUrl('small'),
            'can_be_transparent' => $this->isSvg() || $this->extensionIsOneOf(['svg', 'png', 'webp', 'avif']),
            'alt' => $this->alt,
            'orientation' => $this->orientation(),
        ];
    }

    private function getVideoThumbnail(): array
    {
        return [
            'thumbnail' => $this->thumbnailUrl('small'),
        ];
    }

    private function runAssetHook(): array
    {
        $payload = $this->runHooksWith('asset', [
            'data' => new Fluent,
        ]);

        return $payload->data->toArray();
    }
}
