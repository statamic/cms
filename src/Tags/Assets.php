<?php

namespace Statamic\Tags;

use Statamic\Assets\AssetCollection;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
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

        if (! $id && ! $path) {
            \Log::debug('No asset container ID or path was specified.');

            return;
        }

        if (! $id) {
            throw new \Exception('TODO: Support assets by path.');
        }

        $container = AssetContainer::find($id);

        if (! $container) {
            return $this->parseNoResults();
        }

        $this->assets = $container->assets($this->params->get('folder'), $this->params->get('recursive', false));

        return $this->output();
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
}
