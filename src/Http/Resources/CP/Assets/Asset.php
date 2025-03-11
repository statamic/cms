<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Statamic\Assets\LocalizedAsset;
use Statamic\Facades\Action;
use Statamic\Facades\User;
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
            'lastModified' => $this->lastModified()->inPreferredFormat(),
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
            ->addValues($this->values()->all())
            ->preProcess();

        if ($hasOrigin = $this->hasOrigin()) {
            $originFields = $this->blueprint()->fields()
                ->addValues($this->origin()->values()->all())
                ->preProcess();

            $originValues = $this->origin()->merge($originFields->values());
            $originMeta = $originFields->meta();
        }

        return [
            'values' => $this->data()->merge([
                ...$fields->values(),
                'focus' => $this->asset()->get('focus'),
            ]),
            'meta' => $fields->meta(),
            'localizedFields' => $this->data()->keys()->all(),
            'site' => $this->locale(),
            'isRoot' => $this->locale() === 'default', // todo
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizations' => $this->localizations()
                ->filter(function (LocalizedAsset $localization) {
                    // TODO: Extract this into a policy at some point.
                    $user = User::fromUser(Auth::user());

                    if ($user->isSuper()) {
                        return true;
                    }

                    if (! $user->can('view', $localization->site()->handle())) {
                        return false;
                    }

                    return $user->hasPermission("edit {$this->container()->handle()} assets");
                })
                ->map(function (LocalizedAsset $localization) {
                    return [
                        'handle' => $localization->locale(),
                        'name' => $localization->site()->name(),
                        'active' => $localization->locale() === $this->locale(),
                        'origin' => $localization->isRoot(),
                        'url' => $localization->editUrl(),
                    ];
                })->values()->all(),
        ];
    }
}
