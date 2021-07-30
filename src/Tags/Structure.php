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
        return $this->structure($this->params->get('for'));
    }

    protected function structure($handle)
    {
        if ($handle instanceof StructureContract) {
            $handle = $handle->handle();
        }

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

    public function toArray($tree, $parent = null, $depth = 1)
    {
        return collect($tree)->map(function ($item) use ($parent, $depth) {
            $page = $item['page'];
            $data = $page->toAugmentedArray();
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $data, $depth + 1);

            return array_merge($data, [
                'children'    => $children,
                'parent'      => $parent,
                'depth'       => $depth,
                'is_current'  => rtrim(URL::getCurrent(), '/') == rtrim($page->url(), '/'),
                'is_parent'   => Site::current()->absoluteUrl() === $page->absoluteUrl() ? false : URL::isAncestorOf(URL::getCurrent(), $page->urlWithoutRedirect()),
                'is_external' => URL::isExternal($page->absoluteUrl()),
            ]);
        })->filter()->values()->all();
    }
}
