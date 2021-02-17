<?php

namespace Statamic\Fieldtypes\Assets;

use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Fieldtype;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Support\Str;

class Assets extends Fieldtype
{
    protected $categories = ['media', 'relationship'];
    protected $defaultValue = [];

    protected function configFieldItems(): array
    {
        return [
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.assets.config.mode'),
                'type' => 'select',
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid'),
                    'list' => __('List'),
                ],
                'width' => 50,
            ],
            'container' => [
                'display' => __('Container'),
                'instructions' => __('statamic::fieldtypes.assets.config.container'),
                'type' => 'asset_container',
                'max_items' => 1,
                'mode' => 'select',
                'width' => 50,
            ],
            'folder' => [
                'display' => __('Folder'),
                'instructions' => __('statamic::fieldtypes.assets.config.folder'),
                'type' => 'asset_folder',
                'max_items' => 1,
                'width' => 50,
            ],
            'restrict' => [
                'display' => __('Restrict'),
                'instructions' => __('statamic::fieldtypes.assets.config.restrict'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'allow_uploads' => [
                'display' => __('Allow Uploads'),
                'instructions' => __('statamic::fieldtypes.assets.config.allow_uploads'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'max_files' => [
                'display' => __('Max Files'),
                'instructions' => __('statamic::fieldtypes.assets.config.max_files'),
                'type' => 'integer',
                'width' => 50,
            ],
        ];
    }

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
        if (Str::contains($value, '::')) {
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
        $assets = $this->getAssetsForAugmentation($value);

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    public function shallowAugment($value)
    {
        $assets = $this->getAssetsForAugmentation($value)->map->toShallowAugmentedCollection();

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    private function getAssetsForAugmentation($value)
    {
        return collect($value)->map(function ($path) {
            return $this->container()->asset($path);
        })->filter()->values();
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
            $rules[] = 'max:'.$max;
        }

        return $rules;
    }

    public function fieldRules()
    {
        return collect(parent::fieldRules())->map(function ($rule) {
            $name = Str::before($rule, ':');

            if ($name === 'image') {
                return new ImageRule();
            }

            if ($name === 'mimes') {
                $parameters = explode(',', Str::after($rule, ':'));

                return new MimesRule($parameters);
            }

            return $rule;
        })->all();
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
                    'encoded_asset' => base64_encode($asset->id()),
                    'size' => 'thumbnail',
                ]);
            }

            return $arr;
        });
    }
}
