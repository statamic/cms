<?php

namespace Statamic\Addons\Nav;

use Statamic\API\Structure;

// TODO: Drive out all the functionality.
class ContentTreeBuilder
{
    public function build($params)
    {
        $structure = Structure::find($params['structure']);

        if (!$params['include_home']) {
            $structure->withoutParent();
        }

        return $this->toTree($structure->pages()->all(), 1);
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
