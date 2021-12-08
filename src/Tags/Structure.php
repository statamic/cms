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

class Structure extends Tags
{
    protected $siteCurrentUrl;
    protected $siteAbsoluteUrl;
    protected $augmentKeys;

    public function __construct()
    {
        $this->siteCurrentUrl = URL::getCurrent();
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

        $defaultKeys = ['id', 'permalink', 'title', 'uri', 'url'];
        $this->augmentKeys = $this->params->get('shallow', true)
            ? array_merge($defaultKeys, explode('|', $this->params->get('augment_keys', '')))
            : null;

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
        return collect($tree)->map(function ($item, $index) use ($parent, $depth, $tree) {
            $page = $item['page'];
            $data = $page->toAugmentedArray($this->augmentKeys);
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $data, $depth + 1);

            $currentUrl = $page->urlWithoutRedirect();
            $absoluteUrl = $page->absoluteUrl();

            return array_merge($data, [
                'children'    => $children,
                'parent'      => $parent,
                'depth'       => $depth,
                'index'       => $index,
                'count'       => $index + 1,
                'first'       => $index === 0,
                'last'        => $index === count($tree) - 1,
                'is_current'  => rtrim($this->siteCurrentUrl, '/') === rtrim($currentUrl, '/'),
                'is_parent'   => $this->siteAbsoluteUrl === $absoluteUrl ? false : URL::isAncestorOf($this->siteCurrentUrl, $currentUrl),
                'is_external' => URL::isExternal($absoluteUrl),
            ]);
        })->filter()->values()->all();
    }
}
