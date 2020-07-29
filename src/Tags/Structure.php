<?php

namespace Statamic\Tags;

use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Structures\TreeBuilder;

class Structure extends Tags
{
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
        return $this->structure($this->get('for'));
    }

    protected function structure($handle)
    {
        if ($handle instanceof StructureContract) {
            $handle = $handle->handle();
        }

        $tree = (new TreeBuilder)->build([
            'structure' => $handle,
            'include_home' => $this->get('include_home'),
            'site' => $this->get('site', Site::current()->handle()),
            'from' => $this->get('from'),
        ]);

        return $this->toArray($tree);
    }

    public function toArray($tree, $parent = null, $depth = 1)
    {
        return collect($tree)->map(function ($item) use ($parent, $depth) {
            $page = $item['page'];

            if ($page->reference() && ! $page->referenceExists()) {
                return null;
            }

            if (! $this->get('show_unpublished') && $page->entry() && ! $page->entry()->published()) {
                return null;
            }

            $data = $page->toAugmentedArray();
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $data, $depth + 1);

            return array_merge($data, [
                'children'    => $children,
                'parent'      => $parent,
                'depth'       => $depth,
                'is_current'  => rtrim(URL::getCurrent(), '/') == rtrim($page->url(), '/'),
                'is_parent'   => URL::isAncestor($page->url()),
                'is_external' => URL::isExternal($page->absoluteUrl()),
            ]);
        })->filter()->values()->all();
    }
}
