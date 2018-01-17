<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\Str;
use Statamic\API\Page;
use Statamic\API\Content;

class PagesMode extends AbstractMode
{
    /**
     * Get the suggestions for this mode.
     *
     * @return array
     */
    public function suggestions()
    {
        return (array_get($this->config, 'sort', 'structure') === 'structure')
            ? $this->structuredSuggestions()
            : $this->sortedSuggestions();
    }

    /**
     * Get the suggestions, when sorting pages.
     *
     * @return array
     */
    protected function sortedSuggestions()
    {
        // If a parent has been specified, get it's child pages at
        // the specified depth. Otherwise, just get all pages.
        $pages = ($parent = array_get($this->config, 'parent'))
            ? $this->getPage($parent)->children(array_get($this->config, 'depth'))
            : Page::all();

        $pages = $pages->multisort(array_get($this->config, 'sort', 'title:asc'));

        foreach ($pages as $page) {
            $suggestions[] = [
                'value' => $page->id(),
                'text'  => $this->label($page, 'title')
            ];
        }

        return $suggestions;
    }

    /**
     * Get the suggestions in a structured fashion.
     *
     * @return array
     */
    protected function structuredSuggestions()
    {
        return $this->generateStructureSuggestions($this->getTree())->all();
    }

    /**
     * Generate the suggestions recursively.
     *
     * @param array $tree array
     * @param int $depth int
     * @return \Illuminate\Support\Collection
     */
    protected function generateStructureSuggestions($tree, $depth = 0)
    {
        return collect($tree)->flatMap(function ($item) use ($depth) {
            $indent = $depth > 0 ? str_repeat('  ', $depth - 1) . '↳ ' : '';

            $suggestion = [
                'value' => $item['page']->id(),
                'text'  => $indent . $this->label($item['page'], 'title'),
            ];

            return collect([$suggestion])->merge($this->generateStructureSuggestions(
                array_get($item, 'children'),
                $depth + 1
            ));
        });
    }

    /**
     * Get a page by either a URI or ID.
     *
     * @param string $page  Either a URI or ID.
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    protected function getPage($page)
    {
        return Str::startsWith($page, '/') ? Page::whereUri($page) : Page::find($page);
    }

    /**
     * Get the content tree.
     *
     * @return array
     */
    protected function getTree()
    {
        $parent = $this->getPage(array_get($this->config, 'parent', '/'));
        $tree = Content::tree($parent->uri(), array_get($this->config, 'depth'));

        // If no parent is defined, we need to add the home page since it's not part of the content tree.
        if (! array_has($this->config, 'parent')) {
            array_unshift($tree, [
                'page'     => $parent,
                'depth'    => 1,
                'children' => [],
            ]);
        }

        return $tree;
    }
}
