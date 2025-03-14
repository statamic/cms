<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Support\Str;

class Asset extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id(),
            'path' => $this->path(),
            'filename' => $this->filename(),
            'basename' => $this->basename(),
            'url' => $this->url(),
            'reference' => $this->reference(),
            'permalink' => $this->absoluteUrl(),
            'extension' => $this->extension(),
            'downloadUrl' => $this->cpDownloadUrl(),
            'size' => Str::fileSizeForHumans($this->size()),
            'lastModified' => $this->lastModified()->toIso8601String(),
            'lastModifiedRelative' => $this->lastModified()->diffForHumans(),
            'mimeType' => $this->mimeType(),
            'isImage' => $this->isImage(),
            'isSvg' => $this->isSvg(),
            'isAudio' => $this->isAudio(),
            'isVideo' => $this->isVideo(),
            'isMedia' => $this->isMedia(),
            'isPdf' => $this->isPdf(),
            'isPreviewable' => $this->isPreviewable(),

            $this->mergeWhen($this->hasDimensions(), function () {
                return [
                    'width' => $this->width(),
                    'height' => $this->height(),
                ];
            }),

            $this->mergeWhen($this->hasDuration(), function () {
                return [
                    'duration' => $this->duration(),
                ];
            }),

            $this->mergeWhen($this->isImage() || $this->isSvg(), function () {
                return [
                    'preview' => $this->previewUrl(),
                    'thumbnail' => $this->thumbnailUrl('small'),
                ];
            }),

            $this->mergeWhen($this->isPdf(), function () {
                return [
                    'pdfUrl' => $this->pdfUrl(),
                ];
            }),

            $this->merge($this->publishFormData()),

            'allowDownloading' => $this->container()->allowDownloading(),
            'actionUrl' => cp_route('assets.actions.run'),
            'actions' => Action::for($this->resource, [
                'container' => $this->container()->handle(),
                'folder' => $this->folder(),
                'view' => 'form',
            ]),

            'blueprint' => $this->blueprint()->toPublishArray(),
        ];

        return ['data' => $data];
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
