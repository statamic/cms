<?php

namespace Statamic\Tags;

use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Fields\Value;
use Statamic\Support\Arr;

class Assets extends Tags
{
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
            $value = $value->value();

            if ($value instanceof Builder) {
                $value = $value->get();
            }

            $this->assets = (new AssetCollection([$value]))->flatten();

            return $this->output();
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

        return $this->output();
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

    /**
     * @param $value
     * @return \Illuminate\Support\Collection|mixed|null
     */
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
     * @param  string|array  $urls  One URL, or array of URLs.
     * @return string|void
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

        return $this->output();
    }

    private function output()
    {
        $this->sort();
        $this->limit();

        return $this->assets;
    }

    private function sort()
    {
        if ($sort = $this->params->get('sort')) {
            $this->assets = $this->assets->multisort($sort);
        }
    }

    /**
     * Limit and offset the asset collection.
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
}
