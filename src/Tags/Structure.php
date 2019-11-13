<?php

namespace Statamic\Tags;

use Statamic\Facades\URL;
use Statamic\Facades\Site;
use Statamic\Structures\TreeBuilder;

class Structure extends Tags
{
    public function wildcard($tag)
    {
        return $this->structure($tag);
    }

    public function index()
    {
        return $this->structure($this->get('for'));
    }

    protected function structure($handle)
    {
        $tree = (new TreeBuilder)->build([
            'structure' => $handle,
            'include_home' => $this->get('include_home'),
            'site' => $this->get('site', Site::current()->handle()),
        ]);

        return $this->toArray($tree);
    }

    public function toArray($tree, $parent = null)
    {
        return collect($tree)->map(function ($item) use ($parent) {
            $page = $item['page'];

            if ($page->reference() && !$page->referenceExists()) {
                return null;
            }

            if (! $this->get('show_unpublished') && $page->entry() && !$page->entry()->published()) {
                return null;
            }

            $data = $page->toAugmentedArray();
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $data);

            return array_merge($data, [
                'children'    => $children,
                'parent'      => $parent,
                'is_current'  => rtrim(URL::getCurrent(), '/') == rtrim($page->url(), '/'),
                'is_parent'   => URL::isAncestor($page->uri()),
                'is_external' => URL::isExternal($page->absoluteUrl()),
            ]);
        })->filter()->values()->all();
    }
}
