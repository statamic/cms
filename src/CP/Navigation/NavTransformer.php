<?php

namespace Statamic\CP\Navigation;

use Statamic\Facades\CP\Nav;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class NavTransformer
{
    protected $submitted;
    protected $coreNav;
    protected $config;

    /**
     * Instantiate nav transformer.
     *
     * @param  array  $submitted
     */
    public function __construct($submitted)
    {
        $this->submitted = $submitted;

        $this->coreNav = Nav::buildWithoutPreferences();
    }

    /**
     * Transform and minify from payload submitted by `components/nav/Builder.vue`.
     *
     * @param  array  $submitted
     * @return array
     */
    public static function fromVue(array $submitted)
    {
        return (new static($submitted))
            ->transform()
            ->minify()
            ->get();
    }

    /**
     * Transform from payload submitted by `components/nav/Builder.vue`.
     *
     * @return $this
     */
    public function transform()
    {
        $this->config['reorder'] = $this->itemsAreReordered(
            $this->coreNav->keys(),
            collect($this->submitted)->pluck('section')
        );

        $this->config['sections'] = collect($this->submitted)
            ->keyBy(fn ($section) => $this->transformSectionKey($section['section']))
            ->map(fn ($section) => $this->transformSection($section))
            ->all();

        return $this;
    }

    /**
     * Transform section key.
     *
     * @param  string  $sectionDisplay
     * @return string
     */
    protected function transformSectionKey($sectionDisplay)
    {
        return Str::snake($sectionDisplay);
    }

    /**
     * Transform section.
     *
     * @param  array  $section
     * @return array
     */
    protected function transformSection($section)
    {
        $transformed = [];

        if ($section['section'] !== $section['original']) {
            $transformed['display'] = $section['section'];
        }

        $transformed['reorder'] = $this->itemsAreReordered(
            $this->coreNav->get($section['section'], collect())->map->id(),
            collect($section['manipulations'])->pluck('id')
        );

        $transformed['items'] = collect($section['manipulations'])
            ->keyBy('id')
            ->map(fn ($item) => $this->transformItem($item))
            ->all();

        return $transformed;
    }

    /**
     * Transform nav item.
     *
     * @param  array  $item
     * @return array
     */
    protected function transformItem($item)
    {
        $transformed = $item['manipulations'];

        $children = $this->transformItemChildren($item['children']);
        $childrenHaveManipulations = $this->itemsHaveManipulations($children);

        if (! isset($transformed['action']) && $childrenHaveManipulations) {
            $transformed['action'] = '@modify';
        } elseif (! isset($transformed['action']) && ! $childrenHaveManipulations) {
            $transformed['action'] = '@inherit';
        }

        if ($children) {
            $transformed['children'] = $children;
        }

        return $transformed;
    }

    /**
     * Transform nav item children.
     *
     * @param  array  $children
     * @return array
     */
    protected function transformItemChildren($children)
    {
        return collect($children)
            ->keyBy('id')
            ->map(fn ($item) => $this->transformItem($item))
            ->all();
    }

    /**
     * Check if items are being reordered.
     *
     * @param  array  $originalList
     * @param  array  $newList
     * @return bool
     */
    protected function itemsAreReordered($originalList, $newList)
    {
        return collect($originalList)
            ->zip($newList)
            ->reject(fn ($pair) => is_null($pair->first()))
            ->reject(fn ($pair) => $pair->first() === $pair->last())
            ->isNotEmpty();
    }

    /**
     * Check if items have manipulations.
     *
     * @param  array  $items
     * @return bool
     */
    protected function itemsHaveManipulations($items)
    {
        return collect($items)
            ->pluck('action')
            ->reject(fn ($action) => $action === '@inherit')
            ->isNotEmpty();
    }

    /**
     * Minify tranformed config.
     *
     * @return $this
     */
    public function minify()
    {
        $this->config['sections'] = collect($this->config['sections'])
            ->map(fn ($section) => $this->minifySection($section))
            ->all();

        if ($this->config['reorder'] === true) {
            $this->config = $this->rejectFinalInherits('sections', $this->config);
        } else {
            $this->config = $this->rejectAllInherits('sections', $this->config)['sections'] ?? null;
        }

        return $this;
    }

    /**
     * Minify tranformed section.
     *
     * @param  array  $section
     * @return mixed
     */
    protected function minifySection($section)
    {
        $section['items'] = collect($section['items'])
            ->map(fn ($item) => $this->minifyItem($item))
            ->all();

        if ($section['reorder'] === true) {
            return $this->rejectFinalInherits('items', $section);
        }

        $section = $this->rejectAllInherits('items', $section);

        if (isset($section['display'])) {
            return $section;
        }

        return Arr::get($section, 'items', '@inherit');
    }

    /**
     * Minify tranformed item.
     *
     * @param  array  $item
     * @return array
     */
    protected function minifyItem($item)
    {
        $item = collect($item);

        if ($children = $item->get('children')) {
            $item->put('children', $this->minifyItemChildren($children));
        }

        if ($item->get('action') === '@inherit' || $item->count() === 1) {
            return $item->get('action');
        }

        if ($item->has('children')) {
            $item = $this->rejectAllInherits('children', $item);
        }

        return $item->all();
    }

    /**
     * Minify transformed item children.
     *
     * @param  array  $children
     * @return array
     */
    protected function minifyItemChildren($children)
    {
        return collect($children)
            ->map(fn ($item) => $this->minifyItem($item))
            ->all();
    }

    /**
     * Reject all `@inherit`s.
     *
     * @param  string  $itemsKey
     * @param  array  $data
     * @return array
     */
    protected function rejectAllInherits($itemsKey, $data)
    {
        $data[$itemsKey] = collect($data[$itemsKey])
            ->reject(fn ($item) => $item === '@inherit')
            ->all();

        if (isset($data[$itemsKey]) && count($data[$itemsKey]) === 0) {
            unset($data[$itemsKey]);
        }

        return $data;
    }

    /**
     * Reject final `@inherit`s at end of array.
     *
     * @param  string  $itemsKey
     * @param  array  $data
     * @return array
     */
    protected function rejectFinalInherits($itemsKey, $data)
    {
        $dataOnlyContainsInherits = collect($data[$itemsKey])
            ->reject(fn ($item) => $item === '@inherit')
            ->isEmpty();

        if ($dataOnlyContainsInherits) {
            return $data;
        }

        $continueRejecting = true;

        $data[$itemsKey] = collect($data[$itemsKey])
            ->reverse()
            ->reject(function ($item) use (&$continueRejecting) {
                if ($continueRejecting && $item === '@inherit') {
                    return true;
                }

                return $continueRejecting = false;
            })
            ->reverse()
            ->all();

        return $data;
    }

    /**
     * Get config.
     *
     * @return array
     */
    public function get()
    {
        return $this->config;
    }
}
