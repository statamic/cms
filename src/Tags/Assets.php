<?php

namespace Statamic\Tags;

use Statamic\Assets\Asset as AssetModel;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Facades\Pattern;
use Statamic\Fields\Value;
use Statamic\Support\Arr;

class Assets extends Tags
{
    use Concerns\QueriesConditions,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes;

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
     * @return string
     */
    public function wildcard($method)
    {
        $value = Arr::get($this->context, $this->method);

        if ($this->isAssetsFieldValue($value)) {
            $value = $value->value();

            if ($value instanceof Builder) {
                $value = $value->get();
            }

            $this->assets = (new AssetCollection([$value]))->flatten();

            return $this->outputCollection($this->assets);
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

        if ($collection) {
            return $this->outputCollection($this->assetsFromCollection($collection));
        }

        $this->assets = $this->assetsFromContainer($id, $path);

        if ($this->assets->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->assets;
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

        $query = $container->queryAssets();

        $this->queryFolder($query);
        $this->queryType($query);
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        if ($this->params->get('not_in')) {
            $assets = $this->filterNotIn($query->get());

            return $this->limitCollection($assets);
        }

        if ($limit = $this->params->int('limit')) {
            $query->limit($limit);
        }

        if ($offset = $this->params->int('offset')) {
            $query->offset($offset);
        }

        return $query->get();
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
            if ($type === 'audio') {
                return $value->isAudio();
            }

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
     * Filter out assets from a requested folder.
     */
    private function filterNotIn($assets)
    {
        if (! $not_in = $this->params->get('not_in')) {
            return $assets;
        }

        $regex = '#^('.$not_in.')#';

        // Checking against path for backwards compatibility. Technically folder would be more correct.
        return $assets->reject(fn ($asset) => preg_match($regex, $asset->path()));
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
                'width' => $asset->width(),
                'height' => $asset->height(),
            ];
        });

        return $this->outputCollection($this->assets);
    }

    private function outputCollection($assets)
    {
        $this->assets = $this->filterNotIn($assets);

        if ($sort = $this->params->get('sort')) {
            $this->assets = $this->assets->multisort($sort);
        }

        $this->assets = $this->limitCollection($this->assets);

        if ($this->assets->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->assets;
    }

    private function limitCollection($assets)
    {
        $limit = $this->params->int('limit');
        $limit = ($limit == 0) ? $assets->count() : $limit;
        $offset = $this->params->int('offset');

        return $assets->splice($offset, $limit);
    }

    protected function queryType($query)
    {
        $type = $this->params->get('type');

        if (! $type) {
            return;
        }

        $extensions = match ($type) {
            'audio' => AssetModel::AUDIO_EXTENSIONS,
            'image' => AssetModel::IMAGE_EXTENSIONS,
            'svg' => ['svg'],
            'video' => AssetModel::VIDEO_EXTENSIONS,
            default => [],
        };

        $query->whereIn('extension', $extensions);
    }

    protected function queryFolder($query)
    {
        $folder = $this->params->get('folder');
        $recursive = $this->params->get('recursive', false);

        if ($folder === '/' && $recursive) {
            $folder = null;
        }

        if ($folder === null) {
            return;
        }

        if ($recursive) {
            $query->where('path', 'like', Pattern::sqlLikeQuote($folder).'/%');

            return;
        }

        $query->where('folder', $folder);
    }

    private function isAssetsFieldValue($value)
    {
        return $value instanceof Value
            && optional($value->fieldtype())->handle() === 'assets';
    }
}
