<?php

namespace Statamic\Data\Structures;

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
        ];

        if (Site::hasMultiple()) {
            $data['sites'] = $this->sites;
        } else {
            $data = array_merge($data, $this->in(Site::default()->handle())->fileData());
        }

        return $data;
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
        return Collection::all()->first(function ($collection) {
            return $collection->structure() === $this;
        });
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
}
