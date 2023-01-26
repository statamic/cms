<?php

namespace Statamic\Fieldtypes\Assets;

use Illuminate\Support\Collection;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Assets extends Fieldtype
{
    protected $categories = ['media', 'relationship'];
    protected $defaultValue = [];
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.assets.config.mode'),
                'type' => 'select',
                'default' => 'list',
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
                'default' => AssetContainer::all()->count() == 1 ? AssetContainer::all()->first()->handle() : null,
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
            'show_filename' => [
                'display' => __('Show Filename'),
                'instructions' => __('statamic::fieldtypes.assets.config.show_filename'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'max_files' => [
                'display' => __('Max Files'),
                'instructions' => __('statamic::fieldtypes.assets.config.max_files'),
                'min' => 1,
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
                ? (new AssetResource($asset))->resolve()['data']
                : null;
        })->filter()->values();
    }

    public function augment($values)
    {
        $values = Arr::wrap($values);

        $ids = collect($values)
            ->map(fn ($value) => $this->container()->handle().'::'.$value)
            ->all();

        $query = $this->container()->queryAssets()->whereIn('path', $values);

        $query = new OrderedQueryBuilder($query, $ids);

        return $this->config('max_files') === 1 ? $query->first() : $query;
    }

    public function shallowAugment($values)
    {
        $items = $this->augment($values);

        if ($this->config('max_files') === 1) {
            $items = collect([$items]);
        } else {
            $items = $items->get();
        }

        $items = $items->filter()->map(function ($item) {
            return $item->toShallowAugmentedCollection();
        });

        return $this->config('max_files') === 1 ? $items->first() : $items;
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
        $classes = [
            'dimensions' => DimensionsRule::class,
            'image' => ImageRule::class,
            'max_filesize' => MaxRule::class,
            'mimes' => MimesRule::class,
            'mimetypes' => MimetypesRule::class,
            'min_filesize' => MinRule::class,
        ];

        return collect(parent::fieldRules())->map(function ($rule) use ($classes) {
            $name = Str::before($rule, ':');

            if ($class = Arr::get($classes, $name)) {
                $parameters = explode(',', Str::after($rule, ':'));

                return new $class($parameters);
            }

            return $rule;
        })->all();
    }

    public function preProcessIndex($data)
    {
        return $this->getItemsForPreProcessIndex($data)->map(function ($asset) {
            $arr = [
                'id' => $asset->id(),
                'is_image' => $isImage = $asset->isImage(),
                'is_svg' => $asset->isSvg(),
                'extension' => $asset->extension(),
                'url' => $asset->url(),
            ];

            if ($isImage) {
                $arr['thumbnail'] = cp_route('assets.thumbnails.show', [
                    'encoded_asset' => base64_encode($asset->id()),
                    'size' => 'small',
                ]);
            }

            return $arr;
        });
    }

    protected function getItemsForPreProcessIndex($values): Collection
    {
        if (! $augmented = $this->augment($values)) {
            return collect();
        }

        return $this->config('max_files') === 1 ? collect([$augmented]) : $augmented->get();
    }

    public function toGqlType()
    {
        $type = GraphQL::type(AssetInterface::NAME);

        if ($this->config('max_files') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
