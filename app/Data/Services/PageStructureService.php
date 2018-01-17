<?php

namespace Statamic\Data\Services;

use Statamic\API\Content;
use Statamic\API\Helper;
use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\Path;
use Statamic\API\Stache;
use Statamic\API\Pattern;

class PageStructureService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'pagestructure';

    public function tree(
        $base_url,
        $depth = null,
        $include_entries = false,
        $show_drafts = false,
        $exclude = false,
        $locale = null
    )
    {
        $locale = $locale ?: default_locale();

        // If a localized URL was requested, we'll get the unlocalized version since that's what's stored in the structure array.
        if ($locale !== default_locale()) {
            $base_url = URL::unlocalize($base_url, $locale);
        }

        $structure = $this->all()->map(function ($item, $id) {
            $item = $item->toArray();
            $item['id'] = $id;
            return $item;
        })->keyBy('uri');

        $depth = is_null($depth) ? INF : $depth;
        $output    = [];

        // Exclude URLs
        $exclude = Helper::ensureArray($exclude);

        // No depth asked for
        if ($depth === 0) {
            return [];
        }

        // Make sure we can find the requested URL in the structure
        if (! $structure->has($base_url)) {
            return [];
        }

        // Depth measurements
        $starting_depth  = $structure[$base_url]['depth'] + 1;
        $current_depth   = $starting_depth;

        // Recursively grab the tree
        foreach ($structure->all() as $id => $data) {
            $url = $data['uri'];

            // Is this the right depth and not the 404 page?
            if ($data['depth'] !== $current_depth || $url == "/404") {
                continue;
            }

            // Is this under the appropriate parent?
            if (! Pattern::startsWith(Path::tidy($data['parent'] . '/'), Path::tidy($base_url . '/'))) {
                continue;
            }

            // Is this in the excluded URLs list?
            if (in_array($url, $exclude)) {
                continue;
            }

            // Get information
            $content = $this->getPage($url)->in($locale)->get();
            $content->setSupplement('depth', $current_depth);

            // Draft?
            if (! $show_drafts && ! $content->published()) {
                continue;
            }

            // Get child pages
            $children = $this->tree($url, $depth - 1, $include_entries, $show_drafts, $exclude, $locale);

            // Data to be returned to the tree
            $output[] = [
                'page' => $content,
                'depth' => $current_depth,
                'children' => $children
            ];
        }

        // Sort by page order key
        uasort($output, function($one, $two) {
            return Helper::compareValues($one['page']->order(), $two['page']->order());
        });

        // Merge in any entries on the end if required.
        if ($include_entries) {
            foreach (Page::whereUri($base_url)->entries()->all() as $entry) {
                $output[] = [
                    'page' => $entry->in($locale)->get(),
                    'depth' => $current_depth
                ];
            }
        }

        return array_values($output);
    }

    /**
     * Get a page by URL, with a workaround for a currently unreproducible bug.
     *
     * @return \Statamic\Contracts\Data\Pages\Page
     * @see https://github.com/statamic/v2-hub/issues/1303
     */
    private function getPage($url)
    {
        // If the page doesn't exist, the cache is in the buggy state where the item
        // (correctly) exists in the page structure, but is missing from the meta
        // data. Rebuilding the cache should fix the issue temporarily. Once we
        // track down the cause for the invalid cache, this can be removed.
        if (! $page = Page::whereUri($url)) {
            Stache::clear();
            Stache::update();
            $page = Page::whereUri($url);
        }

        return $page;
    }
}