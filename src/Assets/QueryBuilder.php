<?php

namespace Statamic\Assets;

use Exception;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Assets\QueryBuilder as Contract;
use Statamic\Facades;
use Statamic\Query\IteratorBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder implements Contract
{
    protected $container;
    protected $folder;
    protected $recursive = false;

    protected function getBaseItems()
    {
        $recursive = $this->folder ? $this->recursive : true;

        $cacheKey = 'asset-folder-files-'.$this->getContainer()->handle().'-'.$this->folder;

        $assets = $this->getContainer()->files($this->folder, $recursive);

        if (empty($this->wheres) && $this->limit) {
            $assets = $assets->skip($this->offset)->take($this->limit)->values();
        }

        if ($this->requiresAssetInstances()) {
            $assets = $this->convertPathsToAssets($assets);
        }

        $assets = $this->collect($assets);

        // If any assets were deleted through the filesystem (e.g. manually or
        // through git) during the file listing cache window, the conversion
        // above would have resulted in nulls. We remove the nulls here.
        return $assets->filter()->values();
    }

    protected function limitItems($items)
    {
        if (! empty($this->wheres) || ! $this->limit) {
            return parent::limitItems($items);
        }

        return $items;
    }

    protected function getFilteredItems()
    {
        $items = $this->getBaseItems();

        if ($this->requiresAssetInstances()) {
            $items = $this->filterWheres($items);
        }

        return $items;
    }

    public function get($columns = ['*'])
    {
        $items = parent::get($columns);

        // If we required asset instances, they would have already been converted.
        if ($this->requiresAssetInstances()) {
            return $items;
        }

        $items = $this->convertPathsToAssets($items);

        // If any assets were deleted through the filesystem (e.g. manually or through git)
        // during the file listing cache window, the conversion above would have resulted
        // in nulls. In that case, we'll bust the cache and requery. We also only care
        // when it's being limited like in pagination or when using the take method.
        if ($this->hasAnyNulls($items) && $this->limit) {
            Cache::forget($this->container->filesCacheKey());
            Cache::forget($this->container->filesCacheKey($this->folder));

            return $this->get($columns);
        }

        return $items->filter()->values();
    }

    private function hasAnyNulls($items)
    {
        return $items->reject()->isNotEmpty();
    }

    private function requiresAssetInstances()
    {
        if (! empty($this->wheres)) {
            return true;
        }

        if (! empty($this->orderBys)) {
            return true;
        }

        return false;
    }

    private function convertPathsToAssets($paths)
    {
        return $paths->map(function ($path) {
            return $this->getContainer()->asset($path);
        });
    }

    private function getContainer()
    {
        return $this->container instanceof AssetContainer
            ? $this->container
            : Facades\AssetContainer::find($this->container);
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'container') {
            throw_if($this->container, new Exception('Only one asset container may be queried.'));
            $this->container = $operator;

            return $this;
        }

        if ($column === 'folder') {
            throw_if($this->folder, new Exception('Only one folder may be queried.'));

            if ($operator === 'like') {
                throw_if(starts_with($value, '%'), new Exception('Cannot perform LIKE query on folder with starting wildcard.'));
                $this->folder = str_before($value, '%');
                $this->recursive = true;
            } else {
                $this->folder = $operator;
                $this->recursive = false;
            }

            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    protected function collect($items = [])
    {
        return AssetCollection::make($items);
    }
}
