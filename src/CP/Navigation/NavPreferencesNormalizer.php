<?php

namespace Statamic\CP\Navigation;

use Illuminate\Support\Collection;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class NavPreferencesNormalizer
{
    protected $preferences;
    protected $normalized;

    const ALLOWED_NAV_SECTION_ACTIONS = [
        '@create',   // create custom section
        '@hide',     // hide section
        '@inherit',  // inherit section without modification (used for reordering purposes only, when none of the above apply)
    ];

    const ALLOWED_NAV_SECTION_MODIFICATIONS = [
        'display',   // change section display text
        'items',     // modify section items
        'reorder',   // reorder section items
    ];

    const ALLOWED_NAV_ITEM_ACTIONS = [
        '@create',   // create custom item
        '@hide',     // hide item (only works if item is in its original section)
        '@modify',   // modify item (only works if item is in its original section)
        '@alias',    // alias into another section (can also modify item)
        '@move',     // move into another section (can also modify item)
        '@inherit',  // inherit item without modification (used for reordering purposes only, when none of the above apply)
    ];

    const ALLOWED_NAV_ITEM_MODIFICATIONS = [
        'display',   // change item display text
        'url',       // change item url
        'route',     // change item route (does not currently support parameters)
        'icon',      // change item icon
        'children',  // modify item children
        'reorder',   // reorder item children
    ];

    /**
     * Instantiate nav preferences config helper.
     *
     * @param  array  $navPreferences
     */
    public function __construct($navPreferences)
    {
        $this->preferences = $navPreferences;
    }

    /**
     * Instantiate nav preferences config helper.
     *
     * @param  array  $navPreferences
     * @return array
     */
    public static function fromPreferences($navPreferences)
    {
        return (new static($navPreferences))
            ->normalize()
            ->get();
    }

    /**
     * Normalize config.
     *
     * @return array
     */
    protected function normalize()
    {
        $navConfig = collect($this->preferences);

        $normalized = collect()->put('reorder', (bool) $reorder = $navConfig->get('reorder', false));

        $sections = collect($navConfig->get('sections') ?? $navConfig->except('reorder'));

        $sections = $this
            ->normalizeToInheritsFromReorder($sections, $reorder)
            ->prepend($sections->pull('top_level') ?? '@inherit', 'top_level')
            ->map(fn ($config, $section) => $this->normalizeSectionConfig($config, $section))
            ->reject(fn ($config) => $config['action'] === '@inherit' && ! $reorder)
            ->map(fn ($config) => $this->removeInheritFromConfig($config))
            ->all();

        $normalized->put('sections', $sections);

        $allowedKeys = ['reorder', 'sections'];

        $this->normalized = $normalized->only($allowedKeys)->all();

        return $this;
    }

    /**
     * Normalize section config.
     *
     * @param  mixed  $sectionConfig
     * @param  string  $sectionKey
     * @return array
     */
    protected function normalizeSectionConfig($sectionConfig, $sectionKey)
    {
        $sectionConfig = is_string($sectionConfig)
            ? collect(['action' => Str::ensureLeft($sectionConfig, '@')])
            : collect($sectionConfig);

        $normalized = collect();

        $normalized->put('action', $sectionConfig->get('action'));

        if (! in_array($normalized->get('action'), static::ALLOWED_NAV_SECTION_ACTIONS)) {
            $normalized->put('action', false);
        }

        $normalized->put('display', $sectionConfig->get('display', false));

        $normalized->put('reorder', (bool) $reorder = $sectionConfig->get('reorder', false));

        $items = collect($sectionConfig->get('items') ?? $sectionConfig->except([
            'action',
            'display',
            'reorder',
        ]));

        $items = $this
            ->normalizeToInheritsFromReorder($items, $reorder)
            ->map(fn ($config, $itemId) => $this->normalizeItemConfig($itemId, $config, $sectionKey))
            ->keyBy(fn ($config, $itemId) => $this->normalizeItemId($itemId, $config))
            ->filter()
            ->reject(fn ($config) => $config['action'] === '@inherit' && ! $reorder)
            ->all();

        $normalized->put('items', $items);

        $allowedKeys = array_merge(['action'], static::ALLOWED_NAV_SECTION_MODIFICATIONS);

        return $normalized->only($allowedKeys)->all();
    }

    /**
     * Remove inherit action from config.
     *
     * @param  array  $config
     * @return array
     */
    protected function removeInheritFromConfig($config)
    {
        if ($config['action'] === '@inherit') {
            $config['action'] = false;
        }

        return $config;
    }

    /**
     * Normalize item config.
     *
     * @param  string  $itemId
     * @param  mixed  $itemConfig
     * @param  string  $sectionKey
     * @param  bool  $removeBadActions
     * @return array
     */
    protected function normalizeItemConfig($itemId, $itemConfig, $sectionKey, $removeBadActions = true)
    {
        $normalized = is_string($itemConfig)
            ? collect(['action' => Str::ensureLeft($itemConfig, '@')])
            : collect($itemConfig);

        $isModified = $this->itemIsModified($itemConfig);
        $isInOriginalSection = $this->itemIsInOriginalSection($itemId, $sectionKey);

        // Remove item when not properly using section-specific actions, to ensure the JS nav builder doesn't
        // do unexpected things. See comments on `ALLOWED_NAV_ITEM_ACTIONS` constant at top for details.
        if ($removeBadActions && ! $isInOriginalSection && in_array($normalized->get('action'), ['@hide', '@modify', '@inherit'])) {
            return null;
        }

        // If action is not set, determine the best default action.
        if (! in_array($normalized->get('action'), static::ALLOWED_NAV_ITEM_ACTIONS)) {
            if ($isModified && $isInOriginalSection) {
                $normalized->put('action', '@modify');
            } elseif ($isInOriginalSection) {
                $normalized->put('action', '@inherit');
            } else {
                $normalized->put('action', '@alias');
            }
        }

        // Normalize `reorder` bool.
        if ($reorder = $normalized->get('reorder', false)) {
            $normalized->put('reorder', (bool) $reorder);
        }

        // Normalize `children`.
        $children = $this
            ->normalizeToInheritsFromReorder($normalized->get('children', []), $reorder)
            ->map(fn ($childConfig, $childId) => $this->normalizeChildItemConfig($childId, $childConfig, $sectionKey))
            ->keyBy(fn ($childConfig, $childId) => $this->normalizeItemId($childId, $childConfig))
            ->all();

        // Only output `children` in normalized output if there are any.
        $children
            ? $normalized->put('children', $children)
            : $normalized->forget('children');

        $allowedKeys = array_merge(['action'], static::ALLOWED_NAV_ITEM_MODIFICATIONS);

        return $normalized->only($allowedKeys)->all();
    }

    /**
     * Normalize item ID.
     *
     * @param  string  $id
     * @param  array  $config
     * @return string
     */
    protected function normalizeItemId($id, $config)
    {
        if (Arr::get($config, 'action') === '@alias') {
            return NavTransformer::uniqueId($id);
        }

        return $id;
    }

    /**
     * Normalize child item config.
     *
     * @param  string  $itemId
     * @param  mixed  $itemConfig
     * @param  string  $sectionKey
     * @return array
     */
    protected function normalizeChildItemConfig($itemId, $itemConfig, $sectionKey)
    {
        if (is_string($itemConfig) && ! Str::startsWith($itemConfig, '@')) {
            $itemConfig = [
                'action' => '@create',
                'display' => $itemId,
                'url' => $itemConfig,
            ];
        }

        $normalized = $this->normalizeItemConfig($itemId, $itemConfig, $sectionKey, false);

        if (is_array($normalized)) {
            Arr::forget($normalized, 'reorder');
            Arr::forget($normalized, 'children');
        }

        return $normalized;
    }

    /**
     * Determine if config is modifying a nav item.
     *
     * @param  array  $config
     * @return bool
     */
    protected function itemIsModified($config)
    {
        if (is_string($config) || ! $config) {
            return false;
        }

        $possibleModifications = array_merge(static::ALLOWED_NAV_ITEM_MODIFICATIONS);

        return collect($possibleModifications)
            ->intersect(array_keys($config))
            ->isNotEmpty();
    }

    /**
     * Determine if nav item is in original section.
     *
     * @param  string  $itemId
     * @param  string  $currentSectionKey
     * @return bool
     */
    protected function itemIsInOriginalSection($itemId, $currentSectionKey)
    {
        return Str::startsWith($itemId, "$currentSectionKey::");
    }

    /**
     * Normalize to legacy style inherits from new `reorder: []` array schema, introduced to sidestep ordering issues in SQL.
     */
    protected function normalizeToInheritsFromReorder(array|Collection $items, array|bool $reorder): Collection
    {
        if (! is_array($reorder)) {
            return collect($items);
        }

        return collect($reorder)
            ->flip()
            ->map(fn () => '@inherit')
            ->merge($items);
    }

    /**
     * Get normalized preferences.
     *
     * @return array
     */
    protected function get()
    {
        return $this->normalized;
    }
}
