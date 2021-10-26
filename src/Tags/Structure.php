<?php

namespace Statamic\Tags;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;

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

        $tree = (new TreeBuilder)->build($config = [
            'structure' => $handle,
            'include_home' => $this->params->get('include_home'),
            'show_unpublished' => $this->params->get('show_unpublished', false),
            'site' => $this->params->get('site', Site::current()->handle()),
            'from' => $this->params->get('from'),
            'max_depth' => $this->params->get('max_depth'),
        ]);

        $key = 'structure-tag-tree-'.md5(serialize($config));
        $arr = Cache::rememberForever($key, function () use ($tree) {
            return $this->toArray($tree);
        });

        return $this->addDynamicBitsToTree($arr);
    }

    public function toArray($tree, $parent = null, $depth = 1)
    {
        return collect($tree)->map(function ($item, $index) use ($parent, $depth, $tree) {
            $page = $item['page'];
            $data = $page->toAugmentedArray();
            $children = empty($item['children']) ? [] : $this->toArray($item['children'], $data, $depth + 1);

            return array_merge($data, [
                'children'    => $children,
                'parent'      => $parent,
                'depth'       => $depth,
                'index'       => $index,
                'count'       => $index + 1,
                'first'       => $index === 0,
                'last'        => $index === count($tree) - 1,
                'urlWithoutRedirect' => $page->urlWithoutRedirect(),
            ]);
        })->filter()->values()->all();
    }

    private function addDynamicBitsToTree($tree)
    {
        return collect($tree)->map(function ($item) {
            $item['children'] = $this->addDynamicBitsToTree($item['children']);

            $arr = array_merge($item, [
                'is_current'  => rtrim(URL::getCurrent(), '/') == rtrim($item['urlWithoutRedirect'], '/'),
                'is_parent'   => Site::current()->absoluteUrl() === $item['permalink'] ? false : URL::isAncestorOf(URL::getCurrent(), $item['urlWithoutRedirect']),
                'is_external' => URL::isExternal($item['permalink']),
            ]);

            return Arr::except($arr, 'urlWithoutRedirect');
        })->all();
    }
}
