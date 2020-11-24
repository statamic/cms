<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Support\Str;

class Asset extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id(),
            'path' => $this->path(),
            'filename' => $this->filename(),
            'basename' => $this->basename(),
            'url' => $this->url(),
            'permalink' => $this->absoluteUrl(),
            'extension' => $this->extension(),
            'downloadUrl' => cp_route('assets.download', base64_encode($this->id())),
            'size' => Str::fileSizeForHumans($this->size()),
            'lastModified' => $this->lastModified()->inPreferredFormat(),
            'lastModifiedRelative' => $this->lastModified()->diffForHumans(),
            'isImage' => $this->isImage(),
            'isSvg' => $this->isSvg(),
            'isAudio' => $this->isAudio(),
            'isVideo' => $this->isVideo(),
            'isPreviewable' => $this->isPreviewable(),

            $this->mergeWhen($this->isImage(), function () {
                return [
                    'width' => $this->width(),
                    'height' => $this->height(),
                    'preview' => $this->previewUrl(),
                    'thumbnail' => $this->thumbnailUrl('small'),
                ];
            }),

            $this->merge($this->publishFormData()),

            'allowDownloading' => $this->container()->allowDownloading(),
            'runActionUrl' => cp_route('assets.actions.run'),
            'actions' => Action::for($this->resource, ['container' => $this->container()->handle()]),

            'blueprint' => $this->blueprint()->toPublishArray(),
        ];
    }

    protected function previewUrl()
    {
        // Public asset containers can use their regular URLs.
        // Private ones don't have URLs so we'll generate an actual-size "thumbnail".
        return $this->container()->accessible() ? $this->url() : $this->thumbnailUrl();
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
