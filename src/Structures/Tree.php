<?php

namespace Statamic\Structures;

use Statamic\Contracts\Data\Localization;
use Statamic\Data\ExistsAsFile;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Tree implements Localization
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $locale;
    protected $tree = [];
    protected $structure;
    protected $cachedFlattenedPages;

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function structure($structure = null)
    {
        return $this->fluentlyGetOrSet('structure')->args(func_get_args());
    }

    public function tree($tree = null)
    {
        return $this->fluentlyGetOrSet('tree')
            ->getter(function ($tree) {
                $key = "structure-{$this->structure->handle()}-{$this->locale()}-".md5(json_encode($tree));

                return Blink::once($key, function () use ($tree) {
                    return $this->structure->validateTree($tree, $this->locale());
                });
            })
            ->args(func_get_args());
    }

    public function root()
    {
        if (! $this->structure()->expectsRoot()) {
            return null;
        }

        if (! $root = $this->tree()[0] ?? null) {
            return null;
        }

        return $root['entry'];
    }

    public function handle()
    {
        return $this->structure->handle();
    }

    public function route()
    {
        return $this->structure->route($this->locale());
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            rtrim(Stache::store('navigation')->directory(), '/'),
            $this->locale(),
            $this->handle(),
        ]);
    }

    public function parent()
    {
        if (! $this->root()) {
            return null;
        }

        return (new Page)
            ->setTree($this)
            ->setEntry($this->root())
            ->setRoute($this->route())
            ->setDepth(1)
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

    public function page(string $id): ?Page
    {
        return $this->flattenedPages()
            ->filter->reference()
            ->keyBy->reference()
            ->get($id);
    }

    public function save()
    {
        $this->cachedFlattenedPages = null;

        $this
            ->structure()
            ->addTree($this)
            ->save();
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
            $item['children'] = $this->removeEmptyChildren(array_get($item, 'children', []));

            if (empty($item['children'])) {
                unset($item['children']);
            }

            return $item;
        })->all();
    }

    public function showUrl()
    {
        $params = [];

        if (Site::hasMultiple()) {
            $params['site'] = $this->locale();
        }

        return $this->structure->showUrl($params);
    }

    public function editUrl()
    {
        return $this->structure->editUrl();
    }

    public function deleteUrl()
    {
        return $this->structure->deleteUrl();
    }

    public function append($entry)
    {
        $this->tree[] = ['entry' => $entry->id()];

        return $this;
    }

    public function appendTo($parent, $page)
    {
        if ($parent && ! $this->page($parent)) {
            throw new \Exception("Page [{$parent}] does not exist in this structure");
        }

        if (is_string($page)) {
            $page = ['entry' => $page];
        } elseif (is_object($page)) {
            $page = ['entry' => $page->id()];
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

            if ($branch['entry'] === $parent) {
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
        $parent = optional($this->page($entry)->parent());

        if ($parent->id() === $target || $parent->isRoot() && is_null($target)) {
            return $this;
        }

        if ($this->structure()->expectsRoot() && Arr::get($this->tree, '0.entry') === $target) {
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
            if ($branch['entry'] === $entry) {
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
        $blink = $this->structure->handle().'-'.$this->locale();

        $entries = Blink::store('structure-entries')->once($blink, function () {
            $refs = $this->flattenedPages()->map->reference()->filter()->all();

            return \Statamic\Facades\Entry::query()->whereIn('id', $refs)->get()->keyBy->id();
        });

        return $entries->get($entry);
    }
}
