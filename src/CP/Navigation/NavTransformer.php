<?php

namespace Statamic\CP\Navigation;

use Facades\Statamic\CP\Navigation\NavItemIdHasher;
use Statamic\Facades\CP\Nav;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class NavTransformer
{
    protected $coreNav;
    protected $submitted;
    protected $config;
    protected $reorderedMinimums;

    /**
     * Instantiate nav transformer.
     *
     * @param  array  $submitted
     */
    public function __construct(array $submitted)
    {
        $this->coreNav = Nav::buildWithoutPreferences(true);

        $this->submitted = $this->removeEmptyCustomSections($submitted);
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
     * Remove empty custom sections from submitted payload.
     *
     * @param  array  $submitted
     * @return array
     */
    protected function removeEmptyCustomSections($submitted)
    {
        $coreSections = $this->coreNav->pluck('display');

        return collect($submitted)
            ->filter(fn ($section) => Arr::get($section, 'items', []) || $coreSections->contains($section['display_original']))
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
        $action = Arr::get($section, 'action', false);
        $display = Arr::get($section, 'display');
        $displayOriginal = Arr::get($section, 'display_original', $display);

        return NavItem::snakeCase($action === '@create' ? $display : $displayOriginal);
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

        $transformed['action'] = Arr::get($section, 'action', false) ?: '@inherit';

        $display = Arr::get($section, 'display');
        $displayOriginal = Arr::get($section, 'display_original', $display);

        if ($display !== $displayOriginal) {
            $transformed['display'] = $display;
        }

        $items = Arr::get($section, 'items', []);

        $transformed['reorder'] = $this->itemsAreReordered(
            $this->coreNav->pluck('items', 'display_original')->get($displayOriginal, collect())->map->id(),
            collect($items)->pluck('id'),
            $sectionKey
        );

        $transformed['items'] = $this->transformItems($items, $sectionKey);

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
            ->map(fn ($item) => array_merge($item, ['id' => $this->transformItemId($item, $item['id'], $parentId, $items)]))
            ->keyBy('id')
            ->map(fn ($item, $itemId) => $this->transformItem($item, $itemId, $parentId))
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
    protected function transformItemId($item, $id, $parentId, $items)
    {
        $action = Arr::get($item, 'manipulations.action');

        if ($action === '@create') {
            return (new NavItem)->display(Arr::get($item, 'manipulations.display'))->section($parentId)->id();
        }

        if ($action !== '@alias') {
            return $id;
        }

        $itemsWithSameId = collect($items)
            ->filter(fn ($item) => $item['id'] === $id)
            ->count();

        if ($itemsWithSameId > 1) {
            $id = $this->uniqueId($id);
        }

        return $id;
    }

    /**
     * Transform nav item.
     *
     * @param  array  $item
     * @param  string  $itemId
     * @param  string  $parentId
     * @return array
     */
    protected function transformItem($item, $itemId, $parentId)
    {
        $transformed = Arr::get($item, 'manipulations', []);

        if (! isset($transformed['action'])) {
            $transformed['action'] = '@inherit';
        }

        if (isset($transformed['url'])) {
            $transformed['url'] = $this->transformItemUrl($transformed['url']);
        }

        $children = $this->transformItems(Arr::get($item, 'children', []), $itemId);

        $childrenHaveModifications = collect($children)
            ->reject(fn ($item) => $item['action'] === '@inherit')
            ->isNotEmpty();

        if ($childrenHaveModifications && $transformed['action'] === '@inherit') {
            $transformed['action'] = '@modify';
        }

        $originalItem = $this->findOriginalItem($itemId);
        $originalHasChildren = optional($originalItem)->children();

        $transformed['reorder'] = false;

        if ($children && $originalHasChildren && ! in_array($transformed['action'], ['@alias', '@create'])) {
            $transformed['reorder'] = $this->itemsAreReordered(
                $originalItem->resolveChildren()->children()->map->id()->all(),
                collect($children)->keys()->all(),
                $itemId
            );
        }

        if ($transformed['reorder'] && $transformed['action'] === '@inherit') {
            $transformed['action'] = '@modify';
        }

        $transformed['children'] = $children;

        return $transformed;
    }

    /**
     * Transform item url to match NavItem `url()` conventions.
     *
     * @param  string  $url
     * @return string
     */
    protected function transformItemUrl($url)
    {
        if (Str::startsWith($url, url('/'))) {
            $url = str_replace(url('/'), '', $url);
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $cp = config('statamic.cp.route');
        $cp = Str::ensureLeft($cp, '/');
        $cp = Str::ensureRight($cp, '/');

        if (Str::startsWith($url, $cp)) {
            return str_replace($cp, '', $url);
        }

        return $url;
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
            ->intersect($newList)
            ->values()
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

        $this->reorderedMinimums[$parentKey] = max(1, $minimumItemsCount - 1);
    }

    /**
     * Find original item from core nav by ID.
     *
     * @param  string  $id
     * @return NavItem
     */
    protected function findOriginalItem($id)
    {
        return $this->coreNav
            ->flatMap(fn ($section) => $section['items'])
            ->keyBy
            ->id()
            ->get($id);
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

        // If the config is completely null after minifying, ensure we save an empty array.
        // For example, if we're transforming this config for a user's nav preferences,
        // we don't want it falling back to role or default preferences, unless the
        // user explicitly 'resets' their nav customizations in the JS builder.
        if (is_null($this->config)) {
            $this->config = [];
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
    protected function minifyItem($item, $itemKey, $isChild = false)
    {
        $action = Arr::get($item, 'action');

        $item['children'] = collect($item['children'] ?? [])
            ->map(fn ($item, $childId) => $this->minifyItem($item, $childId, true))
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
        if (! $reorderedMinimum = $this->reorderedMinimums[$parentKey] ?? false) {
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

    /**
     * Helper to add hash to ID.
     *
     * @param  string  $id
     * @return string
     */
    public static function uniqueId($id)
    {
        if (preg_match('/.*[^\:]:[^\:]{6}$/', $id)) {
            return $id;
        }

        return NavItemIdHasher::appendHash($id);
    }

    /**
     * Remove alias hash from ID.
     *
     * @param  string  $id
     * @return string
     */
    public static function removeUniqueIdHash($id)
    {
        return preg_replace('/(.*[^\:]):[^\:]{6}(.*)/', '$1$2', $id);
    }
}
