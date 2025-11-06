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

    /**
     * Instantiate nav transformer.
     */
    public function __construct(array $submitted)
    {
        $this->coreNav = Nav::buildWithoutPreferences(true);

        $this->submitted = $this->removeEmptyCustomSections($submitted);
    }

    /**
     * Transform and minify from payload submitted by `components/nav/Builder.vue`.
     *
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
        $this->config['reorder'] = $this->getReorderedItems(
            $this->coreNav->map(fn ($section) => $this->transformSectionKey($section)),
            collect($this->submitted)->map(fn ($section) => $this->transformSectionKey($section)),
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

        if ($display !== $displayOriginal || $transformed['action'] === '@create') {
            $transformed['display'] = $display;
        }

        $items = Arr::get($section, 'items', []);

        $transformed['reorder'] = $this->getReorderedItems(
            $this->coreNav->pluck('items', 'display_original')->get($displayOriginal, collect())->map->id(),
            collect($items)->pluck('id'),
        );

        $transformed['items'] = $this->transformItems($items, $sectionKey);

        return $transformed;
    }

    /**
     * Transform nav item items.
     *
     * @param  array  $items
     * @param  string  $parentId
     * @param  bool  $transformingChildItems
     * @return array
     */
    protected function transformItems($items, $parentId, $transformingChildItems = false)
    {
        return collect($items)
            ->map(fn ($item) => array_merge($item, ['id' => $this->transformItemId($item, $item['id'], $parentId, $items)]))
            ->keyBy('id')
            ->map(fn ($item, $itemId) => $this->transformItem($item, $itemId, $transformingChildItems))
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
     * @param  bool  $isChild
     * @return array
     */
    protected function transformItem($item, $itemId, $isChild = false)
    {
        $transformed = Arr::get($item, 'manipulations', []);

        if ($isChild) {
            Arr::forget($transformed, 'icon');
        }

        if (! isset($transformed['action'])) {
            $transformed['action'] = '@inherit';
        }

        if (isset($transformed['url'])) {
            $transformed['url'] = $this->transformItemUrl($transformed['url']);
        }

        $children = $this->transformItems(Arr::get($item, 'children', []), $itemId, true);

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
            $transformed['reorder'] = $this->getReorderedItems(
                $originalItem->resolveChildren()->children()->map->id()->all(),
                collect($children)->keys()->all(),
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
     * Check if items are being reordered and return minimum list of item keys required to replicate saved order.
     *
     * @param  array  $originalList
     * @param  array  $newList
     */
    protected function getReorderedItems($originalList, $newList): bool|array
    {
        $itemsAreReordered = collect($originalList)
            ->intersect($newList)
            ->values()
            ->zip($newList)
            ->reject(fn ($pair) => is_null($pair->first()))
            ->reject(fn ($pair) => $pair->first() === $pair->last())
            ->isNotEmpty();

        if (! $itemsAreReordered) {
            return false;
        }

        return collect($newList)
            ->take($this->calculateMinimumItemsForReorder($originalList, $newList))
            ->all();
    }

    /**
     * Calculate minimum number of items needed for reorder config.
     *
     * @param  array  $originalList
     * @param  array  $newList
     */
    protected function calculateMinimumItemsForReorder($originalList, $newList): int
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

        return max(1, $minimumItemsCount - 1);
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
        $sections = collect($this->config['sections'])
            ->map(fn ($section) => $this->minifySection($section))
            ->pipe(fn ($sections) => $this->rejectInherits($sections));

        $reorder = collect(Arr::get($this->config, 'reorder') ?: [])
            ->reject(fn ($section) => $section === 'top_level')
            ->values()
            ->all();

        $this->config = $reorder
            ? array_filter(compact('reorder', 'sections'))
            : $sections;

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
     * @return mixed
     */
    protected function minifySection($section)
    {
        $action = Arr::get($section, 'action');

        $section['items'] = collect($section['items'])
            ->map(fn ($item) => $this->minifyItem($item))
            ->pipe(fn ($items) => $this->rejectInherits($items));

        if (! $section['reorder']) {
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
     * @param  bool  $isChild
     * @return array
     */
    protected function minifyItem($item, $isChild = false)
    {
        $item['children'] = collect($item['children'] ?? [])
            ->map(fn ($item) => $this->minifyItem($item, true))
            ->pipe(fn ($items) => $this->rejectInherits($items));

        if (! $item['reorder']) {
            Arr::forget($item, 'reorder');
        }

        if ($isChild) {
            Arr::forget($item, 'icon');
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
    protected function rejectInherits($items)
    {
        $items = collect($items)->reject(fn ($item) => $item === '@inherit');

        if ($items->count() === 0) {
            return null;
        }

        return $items->all();
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
