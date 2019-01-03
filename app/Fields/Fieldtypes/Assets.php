<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Asset;
use Statamic\API\Helper;
use Statamic\Fields\Fieldtype;
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

    public function preProcess($data)
    {
        $max_files = (int) $this->config('max_files');

        if ($max_files === 1 && empty($data)) {
            return $data;
        }

        if (is_null($data)) {
            return [];
        }

        return Helper::ensureArray($data);
    }

    public function process($data)
    {
        $max_files = (int) $this->config('max_files');

        if ($max_files === 1) {
            return array_get($data, 0);
        }

        return $data;
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
                $asset->set('thumbnail', $this->thumbnail($asset, 'small'));
                $asset->set('toenail', $this->thumbnail($asset, 'large'));
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
}
