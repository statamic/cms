<?php

namespace Statamic\Data\Services;

use Statamic\API\Content;
use Statamic\API\Helper;
use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\Path;
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
            $content = Page::whereUri($url)->in($locale)->get();
            $content->setSupplement('depth', $current_depth);

            // Draft?
            if (! $show_drafts && ! $content->published()) {
                continue;
            }

            // Get entries belonging to this page. We'll treat them as child
            // pages and merge them into the children array later.
            $entries = [];
            if ($include_entries) {
                foreach (Page::whereUri($url)->entries()->all() as $entry) {
                    $entries[] = [
                        'page' => $entry->in($locale)->get(),
                        'depth' => $current_depth
                    ];
                }
            }

            // Get child pages
            $children = $this->tree($url, $depth - 1, $include_entries, $show_drafts, $exclude, $locale);

            // Data to be returned to the tree
            $output[] = [
                'page' => $content,
                'depth' => $current_depth,
                'children' => array_merge($children, $entries)
            ];
        }

        // Sort by page order key
        uasort($output, function($one, $two) {
            return Helper::compareValues($one['page']->order(), $two['page']->order());
        });

        return array_values($output);
    }
}