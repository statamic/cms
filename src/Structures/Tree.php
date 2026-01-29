<?php

namespace Statamic\Structures;

use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Structures\Tree as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasDirtyState;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

abstract class Tree implements Contract, Localization
{
    use ExistsAsFile, FluentlyGetsAndSets, HasDirtyState;

    protected $handle;
    protected $locale;
    protected $tree = [];
    protected $cachedFlattenedPages;
    protected $withEntries = false;
    protected $uriCacheEnabled = true;

    public function idKey()
    {
        return 'id';
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    abstract public function structure();

    public function tree($tree = null)
    {
        return $this->fluentlyGetOrSet('tree')
            ->getter(function ($tree) {
                $key = "structure-{$this->handle()}-{$this->locale()}-".md5(json_encode($tree));

                return Blink::once($key, function () use ($tree) {
                    return $this->structure()->validateTree($tree, $this->locale());
                });
            })
            ->args(func_get_args());
    }

    public function root()
    {
        if (! $this->structure()->expectsRoot()) {
            return null;
        }

        return $this->tree()[0] ?? null;
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function route()
    {
        return $this->structure()->route($this->locale());
    }

    public function parent()
    {
        if (! $this->root()) {
            return null;
        }

        $branch = $this->root();

        return (new Page)
            ->setTree($this)
            ->setId($branch[$this->idKey()] ?? null)
            ->setEntry($branch['entry'] ?? null)
            ->setUrl($branch['url'] ?? null)
            ->setTitle($branch['title'] ?? null)
            ->setRoute($this->route())
            ->setDepth(1)
            ->setPageData($branch['data'] ?? [])
            ->setRoot(true);
    }

    public function pages()
    {
        $pages = $this->tree();

        if ($this->root()) {
            $pages = array_slice($pages, 1);
        }

        $pages = (new Pages)
            ->setTree($this)
            ->setPages($pages)
            ->setParent($this->parent())
            ->setDepth(1);

        if ($route = $this->route()) {
            $pages->setRoute($route);
        }

        return $pages;
    }

    public function flattenedPages()
    {
        if ($this->cachedFlattenedPages) {
            return $this->cachedFlattenedPages;
        }

        return $this->cachedFlattenedPages = $this->pages()->flattenedPages();
    }

    public function uris()
    {
        return $this->flattenedPages()->map->uri();
    }

    public function disableUriCache()
    {
        $this->uriCacheEnabled = false;

        return $this;
    }

    public function uriCacheEnabled()
    {
        return $this->uriCacheEnabled;
    }

    public function find($id): ?Page
    {
        return $this->flattenedPages()
            ->keyBy->id()
            ->get($id);
    }

    public function findByEntry($id)
    {
        return $this->flattenedPages()
            ->filter->reference()
            ->keyBy->reference()
            ->get($id);
    }

    public function save()
    {
        if ($this->dispatchSavingEvent() === false) {
            return false;
        }

        $this->cachedFlattenedPages = null;

        Blink::forget('collection-structure-flattened-pages-collection*');
        Blink::forget('collection-structure-tree*');

        $this->repository()->save($this);

        $this->dispatchSavedEvent();

        $this->syncOriginal();

        return true;
    }

    public function delete()
    {
        Blink::forget('collection-structure-tree*');

        $this->repository()->delete($this);

        $this->dispatchDeletedEvent();

        return true;
    }

    abstract protected function repository();

    protected function dispatchSavedEvent()
    {
        //
    }

    protected function dispatchSavingEvent()
    {
        //
    }

    protected function dispatchDeletedEvent()
    {
        //
    }

    public function fileData()
    {
        return [
            'tree' => $this->removeEmptyChildren($this->tree),
        ];
    }

    protected function removeEmptyChildren($array)
    {
        return collect($array)->map(function ($item) {
            $item['children'] = $this->removeEmptyChildren(Arr::get($item, 'children', []));

            if (empty($item['children'])) {
                unset($item['children']);
            }

            return $item;
        })->all();
    }

    public function showUrl()
    {
        $params = [];

        if (Site::multiEnabled()) {
            $params['site'] = $this->locale();
        }

        return $this->structure()->showUrl($params);
    }

    public function editUrl()
    {
        return $this->structure()->editUrl();
    }

    public function deleteUrl()
    {
        return $this->structure()->deleteUrl();
    }

    public function append($entry)
    {
        // Prevent a null from being added to the tree. This is only a workaround
        // since nulls shouldn't have been passed in here in the first place.
        // TODO: fix actual cause.
        if (is_null($entry)) {
            return $this;
        }

        $this->tree[] = ['entry' => $entry->id()];

        return $this;
    }

    public function appendTo($parent, $page)
    {
        if ($parent && ! $this->find($parent)) {
            throw new \Exception("Page [{$parent}] does not exist in this structure");
        }

        // Prevent a null from being added to the tree. This is only a workaround
        // since nulls shouldn't have been passed in here in the first place.
        // TODO: fix actual cause.
        if (is_null($page)) {
            return $this;
        }

        if (is_string($page)) {
            $page = [$this->idKey() => $page];
        } elseif (is_object($page)) {
            $page = [$this->idKey() => $page->id()];
        }

        if ($parent) {
            $this->tree = $this->appendToInBranches($parent, $page, $this->tree);
        } else {
            $this->tree[] = $page;
        }

        return $this;
    }

    private function appendToInBranches($parent, $page, $branches)
    {
        foreach ($branches as &$branch) {
            $children = $branch['children'] ?? [];

            if ($branch[$this->idKey()] == $parent) {
                $children[] = $page;
                $branch['children'] = $children;
                break;
            }

            $children = $this->appendToInBranches($parent, $page, $children);

            if (! empty($children)) {
                $branch['children'] = $children;
            }
        }

        return $branches;
    }

    public function move($entry, $target)
    {
        $parent = optional($this->find($entry)->parent());

        if ($parent->id() == $target || $parent->isRoot() && is_null($target)) {
            return $this;
        }

        if ($this->structure()->expectsRoot() && Arr::get($this->tree, '0.'.$this->idKey()) === $target) {
            throw new \Exception('Root page cannot have children');
        }

        [$match, $branches] = $this->removeFromInBranches($entry, $this->tree);

        $this->tree = $branches;

        return $this->appendTo($target, $match);
    }

    public function remove($entry)
    {
        $id = is_string($entry) ? $entry : $entry->id();

        [, $branches] = $this->removeFromInBranches($id, $this->tree);

        $this->tree = $branches;

        return $this;
    }

    private function removeFromInBranches($entry, $branches)
    {
        $match = null;

        foreach ($branches as $key => &$branch) {
            if ($branch[$this->idKey()] == $entry) {
                $match = $branch;
                unset($branches[$key]);
                break;
            }

            [$m, $children] = $this->removeFromInBranches($entry, $branch['children'] ?? []);

            if ($m) {
                $match = $m;
            }

            if (empty($children)) {
                unset($branch['children']);
            } else {
                $branch['children'] = $children;
            }
        }

        return [$match, array_values($branches)];
    }

    public function entry($entry)
    {
        $blink = Blink::store('structure-entries');

        return $blink->once($entry, function () use ($blink, $entry) {
            if (! $this->withEntries) {
                return Entry::find($entry);
            }

            $refs = $this->flattenedPages()->map->reference()->filter()->all();
            $entries = Entry::query()->whereIn('id', $refs)->get()->keyBy->id()->all();

            $blink->put($entries);

            return $entries[$entry] ?? null;
        });
    }

    public function withEntries()
    {
        $this->withEntries = true;

        return $this;
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return [
            'tree' => $this->tree,
        ];
    }
}
