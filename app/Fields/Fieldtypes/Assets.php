<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Arr;
use Statamic\API\Asset;
use Statamic\API\Helper;
use Statamic\Fields\Fieldtype;
use Statamic\API\AssetContainer;
use Statamic\Assets\AssetCollection;

class Assets extends Fieldtype
{
    protected $categories = ['media', 'relationship'];

    protected $configFields = [
        'container' => ['type' => 'asset_container'],
        'folder' => ['type' => 'asset_folder'],
        'restrict' => ['type' => 'toggle'],
        'max_files' => ['type' => 'integer'],
        'mode' => [
            'type' => 'select',
            'options' => [
                'grid' => 'Grid',
                'list' => 'List',
            ],
        ],
    ];

    public function canHaveDefault()
    {
        return false;
    }

    public function blank()
    {
        return [];
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
        $data = $this->getItemData($this->field->value());

        return compact('data');
    }

    public function getItemData($items)
    {
        $assets = new AssetCollection;

        foreach ($items as $url) {
            if (! $asset = Asset::find($url)) {
                continue;
            }

            if ($asset->isImage()) {
                $asset->setSupplement('thumbnail', $this->thumbnail($asset, 'small'));
                $asset->setSupplement('toenail', $this->thumbnail($asset, 'large'));
            }

            $assets->put($url, $asset);
        }

        return $assets->values();
    }

    protected function thumbnail($asset, $preset = null)
    {
        return cp_route('assets.thumbnails.show', [
            'asset' => base64_encode($asset->id()),
            'size' => $preset
        ]);
    }

    public function augment($value)
    {
        $assets = collect($value)->map(function ($path) {
            return $this->container()->asset($path);
        })->filter()->values();

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    protected function container()
    {
        return AssetContainer::find($this->config('container'));
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
        $data = Arr::wrap($this->augment($data));

        return collect($data)->map(function ($asset) {
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
