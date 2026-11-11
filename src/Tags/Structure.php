<?php

namespace Statamic\Tags;

use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Data\BulkAugmentor;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\NavigationNotFoundException;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Query\ItemQueryBuilder;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\GetsQuerySelectKeys;
use Statamic\Tags\Concerns\QueriesConditions;

class Structure extends Tags
{
    use GetsQuerySelectKeys;
    use QueriesConditions;

    protected $currentUrl;
    protected $siteAbsoluteUrl;

    public function __construct()
    {
        $this->currentUrl = URL::getCurrent();
        $this->siteAbsoluteUrl = Site::current()->absoluteUrl();
    }

    public function wildcard($tag)
    {
        // Allow {{ structure:collection:pages }} rather than needing to use the double colon.
        $handle = str_replace(':', '::', $tag);

        return $this->structure($handle);
    }

    public function index()
    {
        return $this->structure($this->params->get('for'));
    }

    protected function structure($handle)
    {
        if ($handle instanceof StructureContract) {
            $handle = $handle->handle();
        }

        $this->ensureStructureExists($handle);

        $tree = $this->tree($handle);

        if ($tree) {
            $offset = max(0, $this->params->int('offset'));
            $limit = max(0, $this->params->int('limit')) ?: null;

            $tree = array_slice($tree, $offset, $limit);
        }

        $value = $this->toArray($tree);

        if ($this->parser && ($as = $this->params->get('as'))) {
            return [$as => $value];
        }

        return $value;
    }

    /**
     * A page often outputs the same nav more than once (a desktop menu and a
     * mobile one, say), so a tree that has been built is kept for the rest of
     * the request. Parameters that only change how much of the tree is shown
     * aren't part of what identifies it: a tree built to a greater depth, or
     * one that reached the bottom, serves a shallower tag too.
     */
    private function tree(string $handle)
    {
        $maxDepth = (int) $this->params->get('max_depth') ?: null;

        if ($maxDepth < 0 || ! $key = $this->treeCacheKey($handle)) {
            return $this->buildTree($handle, $maxDepth);
        }

        $trees = Blink::store('structure-tag-trees');

        $built = collect($trees->get($key))->first(function ($built) use ($maxDepth) {
            return $built['complete'] || ($maxDepth && $built['max_depth'] >= $maxDepth);
        });

        if ($built) {
            return $this->limitDepth($built['tree'], $maxDepth);
        }

        $tree = $this->buildTree($handle, $maxDepth);

        $trees->put($key, [...$trees->get($key, []), [
            'tree' => $tree,
            'max_depth' => $maxDepth,
            'complete' => ! $maxDepth || ! $this->wasCutShort($tree, $maxDepth),
        ]]);

        return $tree;
    }

    /**
     * Dropped when an entry is saved, since the pages that have been kept carry
     * its data. A worker has no requests to mark where one page ends and the
     * next begins, so it drops them when it moves on to its next job.
     */
    public static function forgetTrees(): void
    {
        Blink::store('structure-tag-trees')->flush();
        Blink::forget('structure-tag-nodes');
    }

    private function buildTree(string $handle, ?int $maxDepth)
    {
        return (new TreeBuilder)->build([
            'structure' => $handle,
            'query' => $this->query($handle),
            'include_home' => $this->params->get('include_home'),
            'site' => $this->params->get('site', Site::current()->handle()),
            'from' => $this->params->get('from'),
            'max_depth' => $maxDepth,
        ]);
    }

    private function treeCacheKey(string $handle): ?string
    {
        $params = $this->params
            ->except(['for', 'as', 'scope', 'max_depth', 'offset', 'limit', 'select', 'include_parents'])
            ->all();

        if (collect($params)->contains(fn ($param) => ! is_null($param) && ! is_scalar($param))) {
            return null;
        }

        ksort($params);

        // The tree's own contents are part of the key, so a tree that is saved
        // partway through a request isn't served from before the change.
        $site = $params['site'] ?? Site::current()->handle();
        $contents = \Statamic\Facades\Structure::find($handle)?->in($site)?->tree();

        return md5(json_encode([static::class, $handle, $site, $params, $contents]));
    }

    private function wasCutShort(?array $tree, int $maxDepth): bool
    {
        return collect($tree)->contains(function ($item) use ($maxDepth) {
            return $item['depth'] >= $maxDepth
                ? $item['page']->hasChildren()
                : $this->wasCutShort($item['children'], $maxDepth);
        });
    }

    private function limitDepth(?array $tree, ?int $maxDepth): ?array
    {
        if (! $tree || ! $maxDepth) {
            return $tree;
        }

        return collect($tree)->map(function ($item) use ($maxDepth) {
            $item['children'] = $item['depth'] >= $maxDepth ? [] : $this->limitDepth($item['children'], $maxDepth);

            return $item;
        })->all();
    }

    protected function ensureStructureExists(string $handle): void
    {
        if (Str::startsWith($handle, 'collection::')) {
            $collection = Str::after($handle, 'collection::');
            throw_unless(Collection::findByHandle($collection), new CollectionNotFoundException($collection));

            return;
        }

        throw_unless(Nav::findByHandle($handle), new NavigationNotFoundException($handle));
    }

    protected function query($handle)
    {
        $query = new ItemQueryBuilder();

        return Str::startsWith($handle, 'collection::')
            ? $this->queryForCollection($query)
            : $this->queryForNav($query);
    }

    protected function queryForCollection($query)
    {
        if (! $this->isQueryingStatus()) {
            $this->queryStatus($query);
        }
        $this->queryConditions($query);

        return $query;
    }

    protected function queryForNav($query)
    {
        return $this->queryStatus($query);
    }

    protected function queryStatus($query)
    {
        if (! $this->params->get('show_unpublished')) {
            $query->whereIn('status', ['published', null]);
        }

        return $query;
    }

    protected function isQueryingStatus()
    {
        return
            $this->isQueryingCondition('status') ||
            $this->isQueryingCondition('published');
    }

    public function toArray($tree, $parent = null, $depth = 1)
    {
        if (! $tree) {
            return [];
        }

        $nodes = $this->nodes($tree);

        $pages = collect($tree)->map(function ($item, $index) use ($depth, $tree, $parent, $nodes) {
            $page = $item['page'];
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $page, $depth + 1);

            ['data' => $data, 'url' => $url, 'absolute_url' => $absoluteUrl, 'is_external' => $isExternal] = $nodes[$index];

            return array_merge($data, [
                'children' => $children,
                'depth' => $depth,
                'index' => $index,
                'count' => $index + 1,
                'first' => $index === 0,
                'last' => $index === count($tree) - 1,
                'is_current' => ! is_null($url) && $url === $this->currentUrl,
                'is_parent' => $this->isParent($url, $absoluteUrl),
                'is_external' => $isExternal,
            ], $this->params->bool('include_parents', true) ? ['parent' => $parent] : []);
        })->filter()->values();

        $this->updateIsParent($pages);

        return $pages->all();
    }

    /**
     * What a page contributes to the output wherever and however often it is
     * shown: its augmented values and its URLs. Kept per page object for the
     * rest of the request, so a tree that is reused isn't augmented again.
     */
    private function nodes(array $tree): array
    {
        $select = $this->params->explode('select');
        $selected = json_encode($select);
        $known = Blink::once('structure-tag-nodes', fn () => new \WeakMap);

        // Reindexed, because the augmentor numbers what it's given from zero.
        $missing = collect($tree)->reject(fn ($item) => isset($known[$item['page']][$selected]))->values()->all();

        foreach (BulkAugmentor::tree($missing, $select)->toArray() as $index => $data) {
            $page = $missing[$index]['page'];
            $absoluteUrl = $page->absoluteUrl();

            $known[$page] = [...($known[$page] ?? []), $selected => [
                'data' => $data,
                'url' => $page->urlWithoutRedirect(),
                'absolute_url' => $absoluteUrl,
                'is_external' => URL::isExternal((string) $absoluteUrl),
            ]];
        }

        return collect($tree)->map(fn ($item) => $known[$item['page']][$selected])->all();
    }

    private function isParent(?string $url, ?string $absoluteUrl): bool
    {
        if (is_null($url)) {
            return false;
        }

        if ($url === '#') {
            return false;
        }

        if ($this->siteAbsoluteUrl === $absoluteUrl) {
            return false;
        }

        return URL::isAncestorOf($this->currentUrl, $url);
    }

    protected function updateIsParent($pages, &$parent = null)
    {
        $pages->transform(function ($page) use (&$parent) {
            $this->updateIsParent(collect($page['children'] ?? []), $page);

            if ($parent && ($page['is_current'] || $page['is_parent'])) {
                $parent['is_parent'] = true;
            }

            return $page;
        });
    }
}
