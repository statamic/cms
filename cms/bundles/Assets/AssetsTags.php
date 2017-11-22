<?php

namespace Statamic\Addons\Assets;

use Statamic\API\Asset;
use Statamic\API\AssetContainer;
use Statamic\API\Helper;
use Statamic\Extend\Tags;
use Statamic\Assets\AssetCollection;

class AssetsTags extends Tags
{
    /**
     * @var AssetCollection
     */
    private $assets;

    /**
     * Iterate over multiple Assets' data from a value
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
        $value = array_get($this->context, $this->tag_method);

        return $this->assets($value);
    }

    /**
     * Iterate over all assets in a container and optionally by folder
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
        $id = $this->get(['container', 'id']);
        $path = $this->get('path');

        if (!$id && !$path) {
            \Log::debug('No asset container ID or path was specified.');
            return;
        }

        // Get the assets (container) by either ID or path.
        $container = ($id) ? AssetContainer::find($id) : AssetContainer::wherePath($path);

        if (! $container) {
            return $this->parseNoResults();
        }

        $this->assets = $container->assets($this->get('folder'), $this->getBool('recursive', false));

        return $this->output();
    }

    /**
     * Perform the asset lookups
     *
     * @param string|array $urls  One URL, or array of URLs.
     * @return string
     */
    protected function assets($urls)
    {
        if (! $urls) {
            return;
        }

        $urls = Helper::ensureArray($urls);

        $this->assets = collect_assets();

        foreach ($urls as $url) {
            if ($asset = Asset::find($url)) {
                $this->assets->push($asset);
            }
        }

        $this->assets->supplement(function ($asset) {
            return [
                'width'  => $asset->width(),
                'height' => $asset->height()
            ];
        });

        return $this->output();
    }

    private function output()
    {
        $this->sort();
        $this->limit();

        return $this->parseLoop($this->assets);
    }

    private function sort()
    {
        if ($sort = $this->get('sort')) {
            $this->assets = $this->assets->multisort($sort);
        }
    }

    /**
     * Limit and offset the asset collection
     *
     * @return array
     */
    private function limit()
    {
        $limit = $this->getInt('limit');
        $limit = ($limit == 0) ? $this->assets->count() : $limit;
        $offset = $this->getInt('offset');

        $this->assets = $this->assets->splice($offset, $limit);
    }
}
