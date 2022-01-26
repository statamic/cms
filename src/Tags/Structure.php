<?php

namespace Statamic\Tags;

use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\NavigationNotFoundException;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\GetsQuerySelectKeys;

class Structure extends Tags
{
    use GetsQuerySelectKeys;

    protected $currentUrl;
    protected $siteAbsoluteUrl;

    public function __construct()
    {
        $this->currentUrl = URL::getCurrent();
        $this->siteAbsoluteUrl = Site::current()->absoluteUrl();
    }

    public function wildcard($tag)
    {
        $handle = $this->context->value($tag, $tag);

        // Allow {{ structure:collection:pages }} rather than needing to use the double colon.
        if (is_string($handle)) {
            $handle = str_replace(':', '::', $tag);
        }

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

        $tree = (new TreeBuilder)->build([
            'structure' => $handle,
            'include_home' => $this->params->get('include_home'),
            'show_unpublished' => $this->params->get('show_unpublished', false),
            'site' => $this->params->get('site', Site::current()->handle()),
            'from' => $this->params->get('from'),
            'max_depth' => $this->params->get('max_depth'),
        ]);

        return $this->toArray($tree);
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

    public function toArray($tree, $parent = null, $depth = 1)
    {
        $pages = collect($tree)->map(function ($item, $index) use ($parent, $depth, $tree) {
            $page = $item['page'];
            $keys = $this->getQuerySelectKeys($page);
            $data = $page->toAugmentedArray($keys);
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $data, $depth + 1);

            $url = $page->urlWithoutRedirect();
            $absoluteUrl = $page->absoluteUrl();

            return array_merge($data, [
                'children'    => $children,
                'parent'      => $parent,
                'depth'       => $depth,
                'index'       => $index,
                'count'       => $index + 1,
                'first'       => $index === 0,
                'last'        => $index === count($tree) - 1,
                'is_current'  => rtrim($this->currentUrl, '/') === rtrim($url, '/'),
                'is_parent'   => $this->siteAbsoluteUrl === $absoluteUrl ? false : URL::isAncestorOf($this->currentUrl, $url),
                'is_external' => URL::isExternal($absoluteUrl),
            ]);
        })->filter()->values();

        $this->addIsParent($pages);

        return $pages->all();
    }

    protected function addIsParent($pages, &$parent = null)
    {
        $pages->transform(function ($page) use (&$parent) {
            $page['is_parent'] = false;

            $this->addIsParent(collect($page['children'] ?? []), $page);

            if ($parent && ($page['is_current'] || $page['is_parent'])) {
                $parent['is_parent'] = true;
            }

            return $page;
        });
    }
}
