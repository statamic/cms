<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\User;
use Statamic\Support\Str;

class AssetsFieldtypeAsset extends JsonResource
{
    use HasThumbnails;

    public function toArray($request)
    {
        $data = [
            'id' => $this->id(),
            'basename' => $this->basename(),
            'extension' => $this->extension(),
            'size' => Str::fileSizeForHumans($this->size()),
            'isImage' => $this->isImage(),
            'url' => $this->url(),
            'downloadUrl' => $this->cpDownloadUrl(),
            'isSvg' => $this->isSvg(),
            'isMedia' => $this->isMedia(),
            'isEditable' => User::current()->can('edit', $this->resource),
            'isViewable' => User::current()->can('view', $this->resource),
            ...$this->thumbnails(),
            ...$this->publishFormData(),
        ];

        return ['data' => $data];
    }

    protected function publishFormData()
    {
        $fields = $this->blueprint()->fields()
            ->addValues($this->data()->all())
            ->preProcess();

        return [
            'values' => $this->data()->merge($fields->values()),
            'meta' => $fields->meta(),
        ];
    }
}
