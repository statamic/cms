<?php

namespace Statamic\Fieldtypes\Assets;

use Statamic\Assets\AssetCollection;
use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Helper;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Statamic;

class Assets extends Fieldtype
{
    protected $categories = ['media', 'relationship'];
    protected $defaultValue = [];

    protected $configFields = [
        'container' => [
            'type' => 'asset_container',
            'max_items' => 1,
            'instructions' => 'The asset container to work with.',
            'width' => 50
        ],
        'mode' => [
            'type' => 'select',
            'default' => 'grid',
            'options' => [
                'grid' => 'Grid',
                'list' => 'List',
            ],
            'instructions' => 'Default layout interface.',
            'width' => 50
        ],
        'folder' => [
            'type' => 'asset_folder',
            'max_items' => 1,
            'instructions' => 'The folder to begin browsing in.',
            'width' => 50
        ],
        'restrict' => [
            'type' => 'toggle',
            'instructions' => 'Prevent users from navigating to other folders.',
            'width' => 50
        ],
        'allow_uploads' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Allow new files to be uploaded?',
            'width' => 50
        ],
         'max_files' => [
            'type' => 'integer',
            'instructions' => 'The maximum number of selectable assets.',
            'width' => 50
        ],
    ];

    public function canHaveDefault()
    {
        return false;
    }

    public function preProcess($values)
    {
        if (is_null($values)) {
            return [];
        }

        return collect($values)->map(function ($value) {
            return $this->valueToId($value);
        })->filter()->values()->all();
    }

    protected function valueToId($value)
    {
        if (str_contains($value, '::')) {
            return $value;
        }

        return optional($this->container()->asset($value))->id();
    }

    public function process($data)
    {
        $max_files = (int) $this->config('max_files');

        $values = collect($data)->map(function ($id) {
            return Asset::find($id)->path();
        });

        return $this->config('max_files') === 1 ? $values->first() : $values->all();
    }

    public function preload()
    {
        return [
            'data' => $this->getItemData($this->field->value() ?? $this->defaultValue),
            'container' => $this->container()->handle(),
        ];
    }

    public function getItemData($items)
    {
        return collect($items)->map(function ($url) {
            return ($asset = Asset::find($url))
                ? (new AssetResource($asset))->resolve()
                : null;
        })->filter()->values();
    }

    public function augment($value)
    {
        $assets = collect($value)->map(function ($path) {
            return $this->container()->asset($path);
        })->filter()->values();

        if (Statamic::shallowAugmentationEnabled()) {
            $assets = $assets->map(function ($asset) {
                return [
                    'id' => $asset->id(),
                    'url' => $asset->url(),
                    'permalink' => $asset->absoluteUrl(),
                ];
            });
        }

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    protected function container()
    {
        if ($configured = $this->config('container')) {
            if ($container = AssetContainer::find($configured)) {
                return $container;
            }

            throw new AssetContainerNotFoundException($configured);
        }

        if (($containers = AssetContainer::all())->count() === 1) {
            return $containers->first();
        }

        throw new UndefinedContainerException;
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($max = $this->config('max_files')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }

    public function preProcessIndex($data)
    {
        if (! $assets = $this->augment($data)) {
            return [];
        }

        if ($this->config('max_files') === 1) {
            $assets = collect([$assets]);
        }

        return $assets->map(function ($asset) {
            $arr = [
                'id' => $asset->id(),
                'is_image' => $isImage = $asset->isImage(),
                'url' => $asset->url(),
            ];

            if ($isImage) {
                $arr['thumbnail'] = cp_route('assets.thumbnails.show', [
                    'asset' => base64_encode($asset->id()),
                    'size' => 'thumbnail',
                ]);
            }

            return $arr;
        });
    }
}
