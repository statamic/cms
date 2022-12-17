<?php

namespace Statamic\CP\Navigation;

use Statamic\Facades\CP\Nav;
use Statamic\Support\Arr;

class NavTransformer
{
    protected $coreNav;
    protected $submitted;
    protected $allowOverriding;
    protected $config;
    protected $reorderedMinimums;

    /**
     * Instantiate nav transformer.
     *
     * @param  array  $submitted
     */
    public function __construct(array $submitted, bool $allowOverriding = true)
    {
        $this->coreNav = Nav::buildWithoutPreferences();

        $this->submitted = $this->removeEmptyCustomSections($submitted);

        $this->allowOverriding = $allowOverriding;
    }

    /**
     * Transform and minify from payload submitted by `components/nav/Builder.vue`.
     *
     * @param  array  $submitted
     * @return array
     */
    public static function fromVue(array $submitted, bool $allowOverriding = true)
    {
        return (new static($submitted, $allowOverriding))
            ->transform()
            ->minify()
            ->get();
    }

    /**
     * Remove empty custom sections from submitted payload.
     *
     * @param  array  $submitted
     * @return array
     */
    protected function removeEmptyCustomSections($submitted)
    {
        $coreSections = $this->coreNav->pluck('display');

        return collect($submitted)
            ->filter(fn ($section) => $section['items'] || $coreSections->contains($section['display_original']))
            ->all();
    }

    /**
     * Transform from payload submitted by `components/nav/Builder.vue`.
     *
     * @return $this
     */
    protected function transform()
    {
        $this->config['reorder'] = $this->itemsAreReordered(
            $this->coreNav->pluck('display_original'),
            collect($this->submitted)->pluck('display_original'),
            'sections'
        );

        $this->config['sections'] = collect($this->submitted)
            ->keyBy(fn ($section) => $this->transformSectionKey($section))
            ->map(fn ($section, $sectionKey) => $this->transformSection($section, $sectionKey))
            ->all();

        return $this;
    }

    /**
     * Transform section key.
     *
     * @param  string  $section
     * @return string
     */
    protected function transformSectionKey($section)
    {
        return NavItem::snakeCase($section['action'] === '@create' ? $section['display'] : $section['display_original']);
    }

    /**
     * Transform section.
     *
     * @param  array  $section
     * @param  string  $sectionKey
     * @return array
     */
    protected function transformSection($section, $sectionKey)
    {
        $transformed = [];

        $transformed['action'] = $section['action'] ?: '@inherit';

        if ($section['display'] !== $section['display_original']) {
            $transformed['display'] = $section['display'];
        }

        $transformed['reorder'] = $this->itemsAreReordered(
            $this->coreNav->pluck('items', 'display_original')->get($section['display_original'], collect())->map->id(),
            collect($section['items'])->pluck('id'),
            $sectionKey
        );

        $transformed['items'] = $this->transformItems($section['items'], $sectionKey);

        return $transformed;
    }

    /**
     * Transform nav item items.
     *
     * @param  array  $items
     * @param  string  $parentId
     * @return array
     */
    protected function transformItems($items, $parentId)
    {
        return collect($items)
            ->keyBy('id')
            ->keyBy(fn ($item, $itemId) => $this->transformItemId($item, $itemId, $parentId))
            ->map(fn ($item, $itemId) => $this->transformItem($item, $itemId))
            ->all();
    }

    /**
     * Transform item ID.
     *
     * @param  string  $item
     * @param  string  $id
     * @param  string  $parentId
     * @return string
     */
    protected function transformItemId($item, $id, $parentId)
    {
        if (Arr::get($item, 'manipulations.action') === '@create') {
            return (new NavItem)->display(Arr::get($item, 'manipulations.display'))->section($parentId)->id();
        }

        return $id;
    }

    /**
     * Transform nav item.
     *
     * @param  array  $item
     * @param  string  $parentId
     * @return array
     */
    protected function transformItem($item, $parentId)
    {
        $transformed = $item['manipulations'];

        $children = $this->transformItems($item['children'], $parentId);

        $childrenHaveManipulations = $this->itemsHaveManipulations($children);

        if (! isset($transformed['action']) && $childrenHaveManipulations) {
            $transformed['action'] = '@modify';
        } elseif (! isset($transformed['action']) && ! $childrenHaveManipulations) {
            $transformed['action'] = '@inherit';
        }

        $transformed['reorder'] = $item['reorder'] ?? false;

        $transformed['children'] = $children ?? [];

        return $transformed;
    }

    /**
     * Check if items are being reordered.
     *
     * @param  array  $originalList
     * @param  array  $newList
     * @param  string  $parentKey
     * @return bool
     */
    protected function itemsAreReordered($originalList, $newList, $parentKey)
    {
        $itemsAreReordered = collect($originalList)
            ->zip($newList)
            ->reject(fn ($pair) => is_null($pair->first()))
            ->reject(fn ($pair) => $pair->first() === $pair->last())
            ->isNotEmpty();

        if ($itemsAreReordered) {
            $this->trackReorderedMinimums($originalList, $newList, $parentKey);
        }

        return $itemsAreReordered;
    }

    /**
     * Track minimum number of items needed for reorder config.
     *
     * @param  array  $originalList
     * @param  array  $newList
     * @param  string  $parentKey
     */
    protected function trackReorderedMinimums($originalList, $newList, $parentKey)
    {
        $continueRejecting = true;

        $minimumItemsCount = collect($originalList)
            ->reverse()
            ->zip(collect($newList)->reverse())
            ->reject(function ($pair) use (&$continueRejecting) {
                if ($continueRejecting && $pair->first() === $pair->last()) {
                    return true;
                }

                return $continueRejecting = false;
            })
            ->count();

        $this->reorderedMinimums[$parentKey] = $minimumItemsCount;
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
    protected function minify()
    {
        $this->config['sections'] = collect($this->config['sections'])
            ->map(fn ($section, $key) => $this->minifySection($section, $key))
            ->all();

        if ($this->config['reorder'] === true) {
            $this->config['sections'] = $this->rejectUnessessaryInherits($this->config['sections'], 'sections');
        } else {
            $this->config = $this->rejectAllInherits($this->config['sections']);
        }

        // If the config is completely null after minifying, ensure `@override` gets saved.
        // For example, if we're transforming this config for a user's nav preferences,
        // we don't want it falling back to role or default preferences, unless the
        // user explicitly 'resets' their nav customizations in the JS builder.
        if ($this->allowOverriding && is_null($this->config)) {
            $this->config = '@override';
        }

        return $this;
    }

    /**
     * Minify tranformed section.
     *
     * @param  array  $section
     * @param  string  $sectionKey
     * @return mixed
     */
    protected function minifySection($section, $sectionKey)
    {
        $action = Arr::get($section, 'action');

        $section['items'] = collect($section['items'])
            ->map(fn ($item, $key) => $this->minifyItem($item, $key))
            ->all();

        if ($section['reorder'] === true) {
            $section['items'] = $this->rejectUnessessaryInherits($section['items'], $sectionKey);
        } else {
            $section['items'] = $this->rejectAllInherits($section['items']);
            Arr::forget($section, 'reorder');
        }

        $section = collect($section)->filter();

        if ($section->count() > 1 && $action === '@inherit') {
            $section->forget('action');
        }

        if ($section->count() === 1 && $section->has('action')) {
            return $section->get('action');
        }

        if ($section->count() === 1 && $section->has('items')) {
            return $section->get('items');
        }

        return $section->all();
    }

    /**
     * Minify tranformed item.
     *
     * @param  array  $item
     * @param  string  $itemKey
     * @return array
     */
    protected function minifyItem($item, $itemKey)
    {
        $action = Arr::get($item, 'action');

        $isChild = preg_match('/[^:]*::[^:]*::[^:]*/', $itemKey);

        $item['children'] = collect($item['children'] ?? [])
            ->map(fn ($item, $childId) => $this->minifyItem($item, $childId))
            ->all();

        if ($item['reorder'] === true) {
            $item['children'] = $this->rejectUnessessaryInherits($item['children'], $itemKey);
        } else {
            $item['children'] = $this->rejectAllInherits($item['children']);
            Arr::forget($item, 'reorder');
        }

        if ($isChild) {
            Arr::forget($item, 'children');
        }

        $item = collect($item)->filter();

        if ($item->count() === 1 && $item->has('action')) {
            return $item->get('action');
        }

        if ($item->count() === 1 && $item->has('children')) {
            return $item->get('children');
        }

        return $item->all();
    }

    /**
     * Reject all `@inherit`s.
     *
     * @param  array  $items
     * @return array
     */
    protected function rejectAllInherits($items)
    {
        $items = collect($items)->reject(fn ($item) => $item === '@inherit');

        if ($items->count() === 0) {
            return null;
        }

        return $items->all();
    }

    /**
     * Reject unessessary `@inherit`s at end of array.
     *
     * @param  array  $items
     * @param  string  $parentKey
     * @return array
     */
    protected function rejectUnessessaryInherits($items, $parentKey)
    {
        if (! $reorderedMinimum = Arr::get($this->reorderedMinimums, $parentKey)) {
            return $items;
        }

        $keyValuePairs = collect($items)
            ->map(fn ($item, $key) => ['key' => $key, 'value' => $item])
            ->values()
            ->keyBy(fn ($keyValuePair, $index) => $index + 1);

        $trailingInherits = $keyValuePairs
            ->reverse()
            ->takeUntil(fn ($item) => $item['value'] !== '@inherit');

        $modifiedMinimum = $keyValuePairs->count() - $trailingInherits->count();

        $actualMinimum = max($reorderedMinimum, $modifiedMinimum);

        return collect($items)
            ->take($actualMinimum)
            ->all();
    }

    /**
     * Get config.
     *
     * @return array
     */
    protected function get()
    {
        return $this->config;
    }
}
