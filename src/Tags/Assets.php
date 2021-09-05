<?php

namespace Statamic\Tags;

use Illuminate\Pagination\Paginator;
use Statamic\Assets\AssetCollection;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Fields\Value;
use Statamic\Support\Arr;

class Assets extends Tags
{
    use Concerns\OutputsItems;

    /**
     * @var AssetCollection
     */
    private $assets;

    /**
     * Iterate over multiple Assets' data from a value.
     *
     * Usage:
     * {{ asset:[variable] }}
     *   {{ url }}, etc
     * {{ /asset:[variable] }}
     *
     * @param $method
     * @param $arguments
     * @return string
     */
    public function __call($method, $arguments)
    {
        $value = Arr::get($this->context, $this->method);

        if ($this->isAssetsFieldValue($value)) {
            return $value->value();
        }

        if ($value instanceof Value) {
            $value = $value->value();
        }

        return $this->assets($value);
    }

    /**
     * Iterate over all assets in a container and optionally by folder.
     *
     * Usage:
     * {{ assets path="assets" }}
     *   {{ url }}, etc
     * {{ /assets }}
     *
     * @return string
     */
    public function index()
    {
        $id = $this->params->get(['container', 'handle', 'id']);
        $path = $this->params->get('path');
        $collection = $this->params->get('collection');

        $this->assets = $collection
            ? $this->assetsFromCollection($collection)
            : $this->assetsFromContainer($id, $path);

        if ($this->assets->isEmpty()) {
            return $this->parseNoResults();
        }

        if ($this->params->get('paginate') && ($limit = $this->params->get('limit'))) {
            return $this->output($this->paginate($limit));
        }

        return $this->output($this->results());
    }

    protected function assetsFromContainer($id, $path)
    {
        if (! $id && ! $path) {
            \Log::debug('No asset container ID or path was specified.');

            return collect();
        }

        if (! $id) {
            throw new \Exception('TODO: Support assets by path.');
        }

        $container = AssetContainer::find($id);

        if (! $container) {
            return collect();
        }

        $assets = $container->assets($this->params->get('folder'), $this->params->get('recursive', false));

        return $this->filterByType($assets);
    }

    protected function assetsFromCollection($collection)
    {
        return Entry::whereCollection($collection)
            ->flatMap(function ($entry) {
                return $this->filterByFields($entry)->flatMap(function ($field) {
                    if ($this->isAssetsFieldValue($field)) {
                        return $this->filterByType($field->value());
                    }
                });
            })->unique();
    }

    protected function filterByFields($entry)
    {
        $fields = array_filter(explode('|', $this->params->get('fields')));

        $fields = $fields
            ? $entry->toAugmentedArray($fields)
            : $entry->toAugmentedArray();

        return collect($fields);
    }

    protected function filterByType($value)
    {
        if (is_null($value)) {
            return null;
        }

        $value instanceof \Statamic\Assets\Asset
            ? $value = collect([$value])
            : $value;

        $type = $this->params->get('type');

        if (! $type) {
            return $value;
        }

        return $value->filter(function ($value) use ($type) {
            if ($type === 'image') {
                return $value->isImage();
            }

            if ($type === 'svg') {
                return $value->isSvg();
            }

            if ($type === 'video') {
                return $value->isVideo();
            }

            return false;
        });
    }

    /**
     * Perform the asset lookups.
     *
     * @param string|array $urls  One URL, or array of URLs.
     * @return string
     */
    protected function assets($urls)
    {
        if (! $urls) {
            return;
        }

        $urls = (array) $urls;

        $this->assets = new AssetCollection;

        foreach ($urls as $url) {
            if ($asset = Asset::find($url)) {
                $this->assets->push($asset);
            }
        }

        $this->assets->supplement(function ($asset) {
            return [
                'width'  => $asset->width(),
                'height' => $asset->height(),
            ];
        });

        return $this->output($this->results());
    }

    private function sort()
    {
        if ($sort = $this->params->get('sort')) {
            $this->assets = $this->assets->multisort($sort);
        }
    }

    /**
     * Limit and offset the asset collection.
     *
     * @return array
     */
    private function limit()
    {
        $limit = $this->params->int('limit');
        $limit = ($limit == 0) ? $this->assets->count() : $limit;
        $offset = $this->params->int('offset');

        $this->assets = $this->assets->splice($offset, $limit);
    }

    private function isAssetsFieldValue($value)
    {
        return $value instanceof Value
            && optional($value->fieldtype())->handle() === 'assets';
    }

    protected function paginate(int $perPage = null): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        return $this->paginator(
            $this->assets->forPage($page, $perPage),
            $this->assets->count(),
            $perPage ?: 15,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    protected function paginator($items, $total, $perPage, $currentPage, $options): LengthAwarePaginator
    {
        return app()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    protected function results(): AssetCollection
    {
        return tap($this->assets, function () {
            $this->sort();
            $this->limit();
        });
    }
}
