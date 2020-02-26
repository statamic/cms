<?php

namespace Statamic\Structures;

use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Collection;
use Statamic\Data\ExistsAsFile;
use Illuminate\Support\Traits\Tappable;
use Statamic\Facades\Structure as StructureAPI;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Blink;

class Structure implements StructureContract
{
    use FluentlyGetsAndSets, ExistsAsFile, Tappable;

    protected $title;
    protected $handle;
    protected $sites;
    protected $trees;
    protected $collection;
    protected $collections;
    protected $maxDepth;
    protected $expectsRoot = false;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (func_num_args() === 0) {
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

    public function deleteUrl()
    {
        return cp_route('structures.destroy', $this->handle());
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
            'root' => $this->expectsRoot,
            'path' => $this->initialPath() ?? $this->path(),
            'max_depth' => $this->maxDepth,
            'collections' => $this->collections,
            'trees' => $this->trees()->map->toCacheableArray()->all()
        ];
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('navigation')->directory(), '/'),
            $this->handle
        ]);
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title,
            'collections' => $this->collections,
            'max_depth' => $this->maxDepth,
            'root' => $this->expectsRoot ?: null,
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

    public function collection($collection = null)
    {
        return $this
            ->fluentlyGetOrSet('collection')
            ->args(func_get_args());
    }

    public function isCollectionBased()
    {
        return $this->collection() !== null;
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

    public static function __callStatic($method, $parameters)
    {
        return Facades\Structure::{$method}(...$parameters);
    }
}
