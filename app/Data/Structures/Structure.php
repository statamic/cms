<?php

namespace Statamic\Data\Structures;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\API\Collection;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\API\Structure as StructureAPI;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure implements StructureContract
{
    use FluentlyGetsAndSets, ExistsAsFile;

    protected $title;
    protected $handle;
    protected $sites;
    protected $trees;
    protected $collections;
    protected $maxDepth;
    protected $expectsRoot = false;
    protected $collection;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?: Str::humanize($this->handle());
            })->args(func_get_args());
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                return collect(Site::hasMultiple() ? $sites : [Site::default()->handle()]);
            })
            ->args(func_get_args());
    }

    public function showUrl()
    {
        return cp_route('structures.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('structures.edit', $this->handle());
    }

    public function save()
    {
        StructureAPI::save($this);
    }

    public function toCacheableArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'sites' => $this->sites,
            'expects_root' => $this->expectsRoot,
            'path' => $this->initialPath() ?? $this->path(),
            'max_depth' => $this->maxDepth,
            'collections' => $this->collections,
            'trees' => $this->trees()->map->toCacheableArray()->all()
        ];
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('structures')->directory(), '/'),
            $this->handle
        ]);
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title,
            'collections' => $this->collections,
            'max_depth' => $this->maxDepth,
            'expects_root' => $this->collection() ? $this->expectsRoot : null,
        ];

        if (Site::hasMultiple()) {
            $data['sites'] = $this->sites;
        } else {
            $data = array_merge($data, $this->in(Site::default()->handle())->fileData());
        }

        return $data;
    }

    public function expectsRoot($expectsRoot = null)
    {
        return $this->fluentlyGetOrSet('expectsRoot')->args(func_get_args());
    }

    public function trees()
    {
        return collect($this->trees);
    }

    public function makeTree($site)
    {
        return (new Tree)
            ->locale($site)
            ->structure($this);
    }

    public function addTree($tree)
    {
        $tree->structure($this);

        $this->trees[$tree->locale()] = $tree;

        return $this;
    }

    public function removeTree($tree)
    {
        unset($this->trees[$tree->locale()]);

        return $this;
    }

    public function existsIn($site)
    {
        return isset($this->trees[$site]);
    }

    public function in($site)
    {
        return $this->trees[$site] ?? null;
    }

    public function collections($collections = null)
    {
        return $this
            ->fluentlyGetOrSet('collections')
            ->getter(function ($collections) {
                if ($collection = $this->collection()) {
                    return collect([$collection]);
                }

                return collect($collections)->map(function ($collection) {
                    return Collection::findByHandle($collection);
                });
            })
            ->args(func_get_args());
    }

    public function collection()
    {
        if ($this->collection !== null) {
            return $this->collection;
        }

        $collection = Collection::all()->first(function ($collection) {
            return $collection->structureHandle() === $this->handle();
        });

        return $this->collection = $collection ?: false;
    }

    public function isCollectionBased()
    {
        return $this->collection();
    }

    public function maxDepth($maxDepth = null)
    {
        return $this
            ->fluentlyGetOrSet('maxDepth')
            ->setter(function ($maxDepth) {
                return (int) $maxDepth ?: null;
            })->args(func_get_args());
    }

    public function delete()
    {
        StructureAPI::delete($this);

        return true;
    }

    public function entryUri($entry)
    {
        $page = $this->in($entry->locale())
            ->flattenedPages()
            ->keyBy->reference()
            ->get($entry->id());

        return optional($page)->uri();
    }

    public function updateEntryUris()
    {
        StructureAPI::updateEntryUris($this);

        return $this;
    }

    public static function __callStatic($method, $parameters)
    {
        return API\Structure::{$method}(...$parameters);
    }
}
