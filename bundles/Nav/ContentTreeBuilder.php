<?php

namespace Statamic\Addons\Nav;

use Statamic\API\Structure;

// TODO: Drive out all the functionality.
class ContentTreeBuilder
{
    public function build($params)
    {
        if (! $structure = Structure::find($params['structure'])) {
            return null;
        }

        if (! $tree = $structure->in($params['site'])) {
            return null;
        }

        if (!$params['include_home']) {
            $tree->withoutParent();
        }

        return $this->toTree($tree->pages()->all(), 1);
    }

    protected function toTree($pages, $depth)
    {
        return $pages->keyBy->uri()->map(function ($page) use ($depth) {
            return [
                'page' => $page,
                'depth' => $depth,
                'children' => $this->toTree($page->pages()->all(), $depth + 1)
            ];
        })->all();
    }
}
