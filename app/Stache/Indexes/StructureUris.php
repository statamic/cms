<?php

namespace Statamic\Stache\Indexes;

use Statamic\API\Structure;

class StructureUris extends Index
{
    public function getItems()
    {
        return Structure::all()->flatMap(function ($structure) {
            return $this->structureUris($structure);
        })->all();
    }

    protected function structureUris($structure)
    {
        return $structure->trees()->flatMap(function ($tree) {
            return $this->referencedPageUris($tree);
        });
    }

    protected function referencedPageUris($tree)
    {
        $site = $tree->locale();

        return $tree->flattenedPages()->filter(function ($page) {
            return $page->reference() && $page->referenceExists();
        })->mapWithKeys(function ($page) use ($tree, $site) {
            $key = $site . '::' . $page->uri();
            $value = $tree->handle() . '::' . $page->reference();
            return [$key => $value];
        });
    }
}