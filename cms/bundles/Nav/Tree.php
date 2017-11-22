<?php

namespace Statamic\Addons\Nav;

use Statamic\API\URL;
use Statamic\API\Helper;
use Statamic\API\Pattern;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Pages\Page;

class Tree
{
    /**
     * @var array
     */
    private $branches;

    /**
     * @var string|null
     */
    private $sort = null;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @var Statamic\Data\Filters\ConditionFilterer
     */
    private $filterer;

    /**
     * Create a new tree
     *
     * @param  array  $branches  Tree content structure
     */
    public function __construct(array $branches)
    {
        $this->branches = $branches;
    }

    /**
     * Count the number of branches in the tree
     *
     * @return  int
     */
    public function count($tree = null)
    {
        $tree = $tree ?: $this->branches;

        $count = 0;

        foreach ($tree as $branch) {
            $count++;

            if (! empty($branch['children'])) {
                $count += $this->count($branch['children']);
            }
        }

        return $count;
    }

    /**
     * Determine if the tree is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Specify how the tree will be sorted
     *
     * @param  string  $sort
     */
    public function sort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * Supplement taxonomies on all pages
     *
     * @return void
     */
    public function supplementTaxonomies()
    {
        $this->branches = $this->performTaxonomySupplementing($this->branches);
    }

    /**
     * Recursively supplement taxonomies on pages and their children
     *
     * @param array $branches
     * @return array
     */
    private function performTaxonomySupplementing($branches)
    {
        foreach ($branches as $branch) {
            $branch['page']->supplementTaxonomies();
            if ($branch['page'] instanceof Page) {
                $branch['children'] = $this->performTaxonomySupplementing($branch['children']);
            }
        }

        return $branches;
    }

    /**
     * Filter the branches by conditions
     *
     * @param  array $conditions
     */
    public function filter($conditions)
    {
        $this->filterer = app('Statamic\Data\Filters\ConditionFilterer');

        $this->conditions = $conditions;

        $this->branches = $this->filterBranches($this->branches);
    }

    /**
     * Perform the actual filtering of the branches. Handles recursion.
     *
     * @param  array $branches
     * @return array
     */
    private function filterBranches($branches)
    {
        // Map over the branches, grab the pages and merge into a collection
        $collection = collect_content($branches)->map(function ($branch) {
            return $branch['page'];
        });

        // Perform the filter
        $collection = $this->filterer->filter($collection, $this->conditions);

        // Remove any filtered-out items from the original branches array
        $branches = array_intersect_key($branches, $collection->all());

        // Loop over the branches and do this whole thing again to it's children. Recursion!
        foreach ($branches as &$branch) {
            if (! empty($branch['children'])) {
                $branch['children'] = $this->filterBranches($branch['children']);
            }
        }

        return $branches;
    }

    /**
     * Transform a content tree into a format suitable for templating
     *
     * @param array        $tree
     * @param array|null   $parent
     * @param string|null  $sort
     * @return array
     */
    public function toArray($tree = null, $parent = null, $sort = null)
    {
        $tree = $tree ?: $this->branches;

        $data = [];

        foreach ($tree as $item) {
            /* @var $item \Statamic\Data\Page|\Statamic\Data\Entry */
            $page = $item['page'];

            // Get it going with a transformed version of the content. This will
            // contain things like the front matter, slug, url, path, etc.
            $page_data = $page->toArray();

            // Determine the child data that will be injected here with recursion. We'll pass in this
            // item's data, so that the child will have access to its parent's data at any point.
            $children = (! empty($item['children']))
                        ? $this->toArray($item['children'], $page_data)
                        : array();

            // Add some nav-tag specific data
            $extra = [
                'is_published' => $page->published(),
                'is_page'     => ($page instanceof Page),
                'is_entry'    => ($page instanceof Entry),
                'has_entries' => ($page instanceof Page) ? $page->hasEntries() : false,
                'children'    => $children,
                'parent'      => $parent,
                'is_current'  => URL::getCurrent() == $page->uri(),
                'is_parent'   => URL::isAncestor($page->uri())
            ];

            $data[] = array_merge($page_data, $extra);
        }

        // Sort the tree
        if ($this->sort) {
            uasort($data, function ($one, $two) {
                return Helper::compareValues(array_get($one, $this->sort), array_get($two, $this->sort));
            });
        }

        return $data;
    }
}
