<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Support\Str;

class Asset extends JsonResource
{
    use HasThumbnails;

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
            'isEditable' => User::current()->can('edit', $this->resource),
            'isViewable' => User::current()->can('view', $this->resource),

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

            $this->mergeWhen($this->isPdf(), function () {
                return [
                    'pdfUrl' => $this->pdfUrl(),
                ];
            }),

            $this->merge($this->thumbnails()),
            $this->merge($this->publishFormData()),
            $this->mergeWhen(
                Site::multiEnabled() && $this->container()->localizable(),
                fn () => $this->localizationData()
            ),

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

    protected function publishFormData()
    {
        $asset = $this->resource;
        $asset->hydrate();

        // Use $asset->data directly instead of values() to avoid infinite recursion
        // when sites origin map is cyclic. values() follows origin()->values()
        // recursively; data is populated by cycle-safe dataForLocale().
        $values = ($asset->data ?? collect())->all();

        $fields = $this->blueprint()->fields()
            ->addValues($values)
            ->preProcess();

        return [
            'values' => collect($values)->merge($fields->values())->all(),
            'meta' => $fields->meta()->all(),
        ];
    }

    protected function localizationData()
    {
        if (! Site::multiEnabled() || ! $this->container()->localizable()) {
            return [];
        }

        $originValues = null;
        $originMeta = null;

        if ($this->hasOrigin()) {
            $fields = $this->blueprint()->fields()
                ->addValues($this->originValuesData()->all())
                ->preProcess();

            $originValues = $fields->values()->all();
            $originMeta = $fields->meta()->all();
        }

        return [
            'locale' => $this->locale(),
            'localizedFields' => $this->localizedData()->keys()->values()->all(),
            'hasOrigin' => $this->hasOrigin(),
            'originValues' => $originValues,
            'originMeta' => $originMeta,
            'localizations' => Site::all()->map(function ($site) {
                $localized = $this->in($site->handle());

                return [
                    'handle' => $site->handle(),
                    'name' => $site->name(),
                    'active' => $site->handle() === $this->locale(),
                    'origin' => ! $localized->hasOrigin(),
                ];
            })->values()->all(),
        ];
    }
}
