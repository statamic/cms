<?php

namespace Statamic\Data\Pages;

use Statamic\API\Path;
use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\API\Page as PageAPI;
use Statamic\Contracts\Data\Pages\PageTreeReorderer as ReordererContract;

class PageTreeReorderer implements ReordererContract
{
    /**
     * @param array $tree
     * @return mixed
     */
    public function reorder(array $tree)
    {
        // Generate the path mapping and order them by shallowest url first, then order.
        $items = collect($this->buildPaths($tree))->sort(function($a, $b) {
            return Helper::compareValues(substr_count($b, '/'), substr_count($a, '/'));
        });

        foreach ($items as $uuid => $new_path) {
            $old_path = PageAPI::find($uuid)->path();

            if ($old_path !== $new_path) {
                // We move the directories so that folder.yamls and localized pages go along for the ride.
                Folder::disk('content')->rename(Path::directory($old_path), Path::directory($new_path));
            }
        }

        Folder::disk('content')->deleteEmptySubfolders('pages');
    }

    /**
     * Recursively build the paths needed to rename pages
     *
     * @param array  $tree
     * @param string $parent_path
     * @return array
     */
    private function buildPaths($tree, $parent_path = null)
    {
        $paths = [];

        foreach ($tree as $branch) {
            $builder = app('Statamic\Contracts\Data\Content\PathBuilder');

            $builder->page()
                ->uri($branch['slug'])
                ->published($branch['published'])
                ->order($branch['order'])
                ->extension($branch['extension']);

            if ($parent_path) {
                $builder->parentPath(Path::directory($parent_path));
            }

            $path = $builder->get();

            $paths[$branch['id']] = $path;

            // Recursion for child pages
            if (! empty($branch['items'])) {
                $paths = array_merge($paths, $this->buildPaths($branch['items'], $path));
            }
        }

        return $paths;
    }
}
