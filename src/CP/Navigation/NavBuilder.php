<?php

namespace Statamic\CP\Navigation;

use Exception;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class NavBuilder
{
    protected $items = [];
    protected $pendingItems = [];
    protected $withHidden = false;
    protected $sections = [];
    protected $sectionsOriginalItemIds = [];
    protected $sectionsManipulations = [];
    protected $sectionsOrder = [];
    protected $sectionsWithReorderedItems = [];
    protected $built;

    /**
     * Instantiate nav builder.
     *
     * @param  array  $items
     * @param  bool  $withHidden
     */
    public function __construct($items, $withHidden = false)
    {
        $this->items = $items;
        $this->withHidden = $withHidden;
    }

    /**
     * Build navigation.
     *
     * @param  mixed  $preferences
     * @return \Illuminate\Support\Collection
     */
    public function build($preferences = true)
    {
        if ($preferences === true) {
            $preferences = Preference::get('nav');
        }

        return $this
            ->buildChildren()
            ->validateNesting()
            ->validateViews()
            ->authorizeItems()
            ->authorizeChildren()
            ->syncOriginal()
            ->trackCoreSections()
            ->trackOriginalSectionItems()
            ->applyPreferenceOverrides($preferences)
            ->buildSections()
            ->get();
    }

    /**
     * Build children closures.
     *
     * @return $this
     */
    protected function buildChildren()
    {
        collect($this->items)
            ->filter(fn ($item) => $item->isActive() || $this->withHidden)
            ->each(fn ($item) => $item->resolveChildren());

        return $this;
    }

    /**
     * Validate that nav children don't exceed nesting limit.
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function validateNesting()
    {
        collect($this->items)
            ->flatMap(fn ($item) => $item->children())
            ->filter(fn ($item) => $item->children())
            ->whenNotEmpty(function ($item) {
                throw new Exception('Nav children have exceeded their nesting limit.');
            });

        return $this;
    }

    /**
     * Validate that nav children don't specify views.
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function validateViews()
    {
        collect($this->items)
            ->flatMap(fn ($item) => $item->children())
            ->reject(fn ($item) => is_null($item->view()))
            ->whenNotEmpty(function ($item) {
                throw new Exception('Nav children cannot specify views.');
            });

        return $this;
    }

    /**
     * Authorize nav items.
     *
     * @return $this
     */
    protected function authorizeItems()
    {
        $this->items = $this->filterAuthorizedNavItems($this->items);

        return $this;
    }

    /**
     * Authorize nav children.
     *
     * @return $this
     */
    protected function authorizeChildren()
    {
        collect($this->items)
            ->reject(fn ($item) => is_callable($item->children()))
            ->each(fn ($item) => $item->children($this->filterAuthorizedNavItems($item->children())));

        return $this;
    }

    /**
     * Filter authorized nav items.
     *
     * @param  mixed  $items
     * @return array
     */
    protected function filterAuthorizedNavItems($items)
    {
        return collect($items)
            ->filter(function ($item) {
                return $item->authorization()
                    ? User::current()->can($item->can()->ability, $item->can()->arguments)
                    : true;
            })
            ->all();
    }

    /**
     * Sync original state on each item.
     *
     * @return $this
     */
    protected function syncOriginal()
    {
        collect($this->items)->each(fn ($item) => $item->syncOriginal());

        return $this;
    }

    /**
     * Track core section items.
     *
     * @return $this
     */
    public function trackCoreSections()
    {
        $this->sections = collect($this->items)
            ->reject(fn ($item) => $item->isChild())
            ->map(fn ($item) => $item->section())
            ->unique()
            ->keyBy(fn ($section) => NavItem::snakeCase($section))
            ->all();

        return $this;
    }

    /**
     * Track original section items.
     *
     * @return $this
     */
    protected function trackOriginalSectionItems()
    {
        collect($this->items)
            ->reject(fn ($item) => $item->isChild())
            ->filter(fn ($item) => $item->section())
            ->each(function ($item) use (&$sections) {
                $this->sectionsOriginalItemIds[$item->section()][] = $item->id();
            });

        return $this;
    }

    /**
     * Apply overrides from user preferences.
     *
     * @param  mixed  $preferences
     * @return $this
     */
    protected function applyPreferenceOverrides($preferences = null)
    {
        if (! $preferences) {
            return $this;
        }

        $navPreferencesConfig = NavPreferencesNormalizer::fromPreferences($preferences);

        $sections = collect($navPreferencesConfig['sections'])
            ->map(fn ($overrides, $section) => $this->ensureSectionConfigHasDisplay($section, $overrides));

        $sections
            ->each(fn ($overrides, $section) => $this->trackSectionManipulations($section, $overrides))
            ->filter(fn ($overrides, $section) => $this->isSectionRenamed($section))
            ->each(fn ($overrides, $section) => $this->renameSection($section, $overrides['display']));

        $sections
            ->each(fn ($overrides) => $this->createPendingItemsForSection($overrides))
            ->each(fn ($overrides) => $this->applyPreferenceOverridesForSection($overrides));

        if ($navPreferencesConfig['reorder']) {
            $this->setSectionOrder($sections);
        }

        return $this;
    }

    /**
     * Create pending items for specific section ahead of time, so that they can be aliased, etc. from anywhere in nav.
     *
     * @param  array  $sectionNav
     */
    protected function createPendingItemsForSection($sectionNav)
    {
        $section = $sectionNav['display'];

        collect($sectionNav['items'])
            ->filter(fn ($config) => $config['action'] === '@create')
            ->each(fn ($config) => $this->userCreatePendingItem($config, $section));

        collect($sectionNav['items'])
            ->keyBy(function ($config, $key) {
                if ($config['action'] === '@create') {
                    return $config['display'] ?? $key;
                } else {
                    return $key;
                }
            })
            ->map(fn ($config) => $config['children'] ?? null)
            ->filter()
            ->each(fn ($children, $parentKey) => $this->createPendingItemsForChildren($children, $section, $parentKey));
    }

    /**
     * Create pending items for an item's children ahead of time, so that they can be aliased, etc. from anywhere in nav.
     *
     * @param  array  $children
     * @param  string  $section
     * @param  string  $parentKey
     */
    protected function createPendingItemsForChildren($children, $section, $parentKey)
    {
        $parentKey = Str::contains($parentKey, '::')
            ? $parentKey
            : $section.'::'.$parentKey;

        collect($children)
            ->filter(fn ($config) => $config['action'] === '@create' && isset($config['display']))
            ->keyBy(fn ($config) => $this->generateNewItemId($parentKey, $config['display']))
            ->each(fn ($config, $id) => $this->userCreatePendingItem($config, $section, $id));
    }

    /**
     * Apply user preference overrides for specific section.
     *
     * @param  array  $sectionNav
     */
    protected function applyPreferenceOverridesForSection($sectionNav)
    {
        $section = $sectionNav['display'];

        collect($sectionNav['items'])
            ->map(fn ($config, $id) => $this->applyPreferenceOverridesForSectionItem($config, $section, $id))
            ->filter()
            ->each(fn ($item) => $item->isChild(false));

        if ($sectionNav['reorder']) {
            $this->setSectionItemOrder($section, $sectionNav['items']);
        }
    }

    /**
     * Apply user preference overrides for specific section.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  string  $id
     * @return \Statamic\CP\Navigation\NavItem|null
     */
    protected function applyPreferenceOverridesForSectionItem($config, $section, $id)
    {
        if (! $item = $this->applyPreferenceOverrideForItem($config, $section, $this->findItem($id), $id)) {
            return;
        }

        if (! in_array($item->manipulations()['action'], ['@modify', '@hide'])) {
            $this->items[] = $item;
        }

        return $item;
    }

    /**
     * Apply preference overide for specific item.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  \Statamic\CP\Navigation\NavItem|null  $item
     * @param  string|null  $id
     * @return \Statamic\CP\Navigation\NavItem|null
     */
    protected function applyPreferenceOverrideForItem($config, $section, $item = null, $id = null)
    {
        switch ($config['action']) {
            case '@create':
                return $this->userCreateFromPendingItem($config, $section, $id);
            case '@hide':
                return $this->userHideItem($item, $config, $section);
            case '@modify':
                return $this->userModifyItem($item, $config, $section);
            case '@alias':
                return $this->userAliasItem($item, $config, $section, $id);
            case '@move':
                return $this->userMoveItem($item, $config, $section);
        }
    }

    /**
     * Ensure section config has display.
     *
     * @param  string  $sectionKey
     * @param  array  $sectionConfig
     * @return array
     */
    protected function ensureSectionConfigHasDisplay($sectionKey, $sectionConfig)
    {
        // If section already has explicit display value...
        if ($sectionConfig['display'] !== false) {
            return $sectionConfig;
        }

        // If section display not being renamed, attempt to find it from registered items...
        if ($registeredDisplay = collect($this->sections)->get($sectionKey)) {
            return array_merge($sectionConfig, ['display' => $registeredDisplay]);
        }

        // Otherwise infer display from section key...
        return array_merge($sectionConfig, ['display' => Str::modifyMultiple($sectionKey, ['deslugify', 'title'])]);
    }

    /**
     * Track section manipulations.
     *
     * @param  string  $sectionKey
     * @param  array  $overrides
     */
    protected function trackSectionManipulations($sectionKey, $overrides)
    {
        $this->sectionsManipulations[$sectionKey] = [
            'action' => $overrides['action'],
            'display' => $overrides['display'],
            'display_original' => null,
        ];
    }

    /**
     * Check if section is being renamed.
     *
     * @param  string  $sectionKey
     * @return bool
     */
    protected function isSectionRenamed($sectionKey)
    {
        if (! $displayOriginal = collect($this->sections)->get($sectionKey)) {
            return false;
        }

        return $displayOriginal !== $this->sectionsManipulations[$sectionKey]['display'];
    }

    /**
     * Rename section.
     *
     * @param  string  $sectionKey
     */
    protected function renameSection($sectionKey)
    {
        $displayOriginal = collect($this->sections)->get($sectionKey);
        $displayNew = $this->sectionsManipulations[$sectionKey]['display'];

        collect($this->items)
            ->filter(fn ($item) => $item->section() === $displayOriginal)
            ->each(fn ($item) => $item->preserveCurrentId()->section($displayNew));

        $this->sections[$sectionKey] = $displayNew;
        $this->sectionsManipulations[$sectionKey]['display_original'] = $displayOriginal;
    }

    /**
     * Set section order.
     *
     * @param  array  $sections
     */
    protected function setSectionOrder($sections)
    {
        // Get conconfigured core sections...
        $unconfiguredCoreSections = $this->sections;

        // Get unconfigured sections...
        $unconfiguredRegisteredSections = collect($this->items)->map->section()->filter()->unique();

        // Merge unconfigured sections onto the end of the list and map their order...
        $this->sectionsOrder = collect($sections)
            ->pluck('display')
            ->merge($unconfiguredRegisteredSections)
            ->merge($unconfiguredCoreSections)
            ->unique()
            ->values()
            ->mapWithKeys(fn ($section, $index) => [$section => $index + 1])
            ->all();
    }

    /**
     * Set section item order.
     *
     * @param  string  $section
     * @param  array  $items
     */
    protected function setSectionItemOrder($section, $items)
    {
        // Generate IDs for newly created items...
        $itemIds = collect($items)
            ->map(function ($item, $id) use ($section, $items) {
                return $items[$id]['action'] === '@create'
                    ? $this->generateNewItemId($section, $items[$id]['display'])
                    : $id;
            })
            ->values();

        // Get unconfigured item IDs...
        $unconfiguredItemIds = collect($this->items)
            ->filter(fn ($item) => $item->section() === $section)
            ->map
            ->id();

        // Merge unconfigured items into the end of the list...
        $itemIds = $itemIds
            ->values()
            ->merge($unconfiguredItemIds)
            ->unique()
            ->values();

        // Set an explicit order value on each item...
        $itemIds
            ->map(fn ($id) => $this->findItem($id, false))
            ->filter()
            ->each(fn ($item, $index) => $item->order($index + 1));

        // Inform builder that section items should be ordered...
        $this->sectionsWithReorderedItems[] = $section;
    }

    /**
     * Find existing nav item by ID.
     *
     * @param  string  $id
     * @param  bool  $removeAliasHash
     * @return \Statamic\CP\Navigation\NavItem|null
     */
    protected function findItem($id, $removeAliasHash = true)
    {
        if ($removeAliasHash) {
            $id = NavTransformer::removeUniqueIdHash($id);
        }

        if ($item = collect($this->pendingItems)->get($id)) {
            return $item;
        }

        $items = collect($this->items)->keyBy->id();

        if ($item = $items->get($id)) {
            return $item;
        }

        if ($parent = $this->findParentItem($id)) {
            if ($children = $parent->resolveChildren()->children()) {
                $children->each(fn ($item) => $items->put($item->id(), $item));
            }
        }

        return $items->get($id);
    }

    /**
     * Find parent nav item by ID.
     *
     * @param  string  $id
     * @param  bool  $removeAliasHash
     * @return \Statamic\CP\Navigation\NavItem|null
     */
    protected function findParentItem($id, $removeAliasHash = true)
    {
        if ($removeAliasHash) {
            $id = NavTransformer::removeUniqueIdHash($id);
        }

        $items = collect($this->items)->keyBy->id();

        $idParts = collect(explode('::', $id));

        if ($idParts->count() < 3) {
            return null;
        }

        $parentId = $idParts[0].'::'.$idParts[1];

        return $items->get($parentId);
    }

    /**
     * Create new NavItem from user config.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  string  $section
     * @return \Statamic\CP\Navigation\NavItem
     */
    protected function userCreatePendingItem($config, $section, $id = null)
    {
        $config = collect($config);

        if (! $display = $config->get('display')) {
            return;
        }

        $item = (new NavItem)->display($display)->section($section);

        if ($id) {
            $item->id($id);
        }

        if ($children = $config->get('children')) {
            $this->createPendingItemsForChildren($children, $section, $item->id());
        }

        $item->syncOriginal();

        $this->userModifyItem($item, $config, $section);

        $this->pendingItems[NavTransformer::removeUniqueIdHash($item->id())] = $item;

        return $item;
    }

    /**
     * Create new NavItem from pending created item.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  string  $id
     * @return \Statamic\CP\Navigation\NavItem|null
     */
    protected function userCreateFromPendingItem($config, $section, $id = null)
    {
        $config = collect($config);

        if (! $display = $config->get('display')) {
            return;
        }

        if ($pendingItem = collect($this->pendingItems)->get($id)) {
            return $pendingItem;
        }

        $id = $this->generateNewItemId($section, $display);

        return collect($this->pendingItems)->get($id);
    }

    /**
     * Hide NavItem.
     *
     * @param  \Statamic\CP\Navigation\NavItem  $item
     */
    protected function userHideItem($item, $config, $section)
    {
        if (is_null($item)) {
            return;
        }

        $item->manipulations($config);

        if ($this->withHidden) {
            return $item;
        }

        $item->hidden(true);

        $this->userRemoveItemFromChildren($item);
    }

    /**
     * Modify NavItem.
     *
     * @param  \Statamic\CP\Navigation\NavItem  $item
     * @param  array  $config
     * @param  string  $section
     */
    protected function userModifyItem($item, $config, $section)
    {
        if (is_null($item)) {
            return;
        }

        $item->preserveCurrentId();

        $item->manipulations(collect($config)->except('children')->all());

        $config = collect($config);

        collect(NavPreferencesNormalizer::ALLOWED_NAV_ITEM_MODIFICATIONS)
            ->filter(fn ($setter) => $config->has($setter))
            ->mapWithKeys(fn ($setter) => [$setter => $config->get($setter)])
            ->reject(fn ($value, $setter) => in_array($setter, ['children', 'reorder']))
            ->each(fn ($value, $setter) => $item->{$setter}($value));

        if ($children = $config->get('children')) {
            $this->userModifyItemChildren($item, $children, $section, $config->get('reorder'));
        }

        return $item;
    }

    /**
     * Modify NavItem children.
     *
     * @param  \Statamic\CP\Navigation\NavItem  $item
     * @param  array  $childrenOverrides
     * @param  string  $section
     * @return \Illuminate\Support\Collection
     */
    protected function userModifyItemChildren($item, $childrenOverrides, $section, $reorder)
    {
        $itemChildren = collect($item->original()->resolveChildren()->children())
            ->each(fn ($item, $index) => $item->order($index + 1000))
            ->keyBy
            ->id();

        collect($childrenOverrides)
            ->map(fn ($config, $key) => $this->userModifyChild($config, $section, $key, $item))
            ->each(function ($item, $key) use (&$itemChildren) {
                $item
                    ? $itemChildren->put($key, $item)
                    : $itemChildren->forget($key);
            })
            ->filter()
            ->values()
            ->each(fn ($item, $index) => $item->order($index + 1));

        $newChildren = $reorder
            ? $itemChildren->sortBy(fn ($item) => $item->order())->values()
            : $itemChildren->values();

        $newChildren->each(fn ($item, $index) => $item->order($index + 1));

        $item->children($newChildren, false);

        return $newChildren;
    }

    /**
     * Modify child NavItem.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  string  $key
     * @param  \Statamic\CP\Navigation\NavItem  $parentItem
     * @return mixed
     */
    protected function userModifyChild($config, $section, $key, $parentItem)
    {
        $item = $this->findItem($key);

        if ($config['action'] === '@inherit') {
            return $item;
        }

        $id = NavTransformer::removeUniqueIdHash($parentItem->id());

        if ($config['action'] === '@create' && isset($config['display'])) {
            $id = $this->generateNewItemId($id, $config['display']);
        }

        if ($childItem = $this->applyPreferenceOverrideForItem($config, $section, $item, $id ?? null)) {
            $childItem->isChild(true);
            $childItem->children([]);
        }

        return $childItem;
    }

    /**
     * Create alias for NavItem.
     *
     * @param  \Statamic\CP\Navigation\NavItem  $item
     * @param  array  $config
     * @param  string  $section
     * @param  string  $id
     * @param  bool  $resetChildren
     */
    protected function userAliasItem($item, $config, $section, $id, $resetChildren = true)
    {
        if (is_null($item)) {
            return;
        }

        $clone = clone $item;

        if ($clone->original()) {
            $clone->original()->preserveCurrentId();
        }

        $clone->id(NavTransformer::uniqueId($id ?? $item->id()));
        $clone->section($section);
        $clone->manipulations($config);

        if ($resetChildren && $clone->original()) {
            $clone->original()->children([]);
        }

        if ($resetChildren) {
            $clone->children([]);
        }

        $this->userModifyItem($clone, $config, $section);

        return $clone;
    }

    /**
     * Move NavItem to new section.
     *
     * @param  \Statamic\CP\Navigation\NavItem  $item
     * @param  array  $config
     * @param  string  $section
     */
    protected function userMoveItem($item, $config, $section)
    {
        if (is_null($item)) {
            return;
        }

        $clone = $this->userAliasItem($item, $config, $section, NavTransformer::uniqueId($item->id()), false);

        $this->userRemoveItem($item);

        $clone->id($item->id());

        return $clone;
    }

    /**
     * Remove NavItem completely, so that it's not even visible when the `$withHidden` flag is set.
     *
     * @param  mixed  $item
     */
    protected function userRemoveItem($item)
    {
        $this->userRemoveItemFromChildren($item);

        $this->items = collect($this->items)
            ->reject(fn ($registeredItem) => $registeredItem->id() === $item->id())
            ->all();
    }

    /**
     * Remove NavItem from parent's children.
     *
     * @param  mixed  $item
     */
    protected function userRemoveItemFromChildren($item)
    {
        $parent = $this->findParentItem($item->id(), false);

        if (! $parent) {
            return;
        }

        if ($parent->resolveChildren()->children()) {
            $parent->children(
                $parent->children()->reject(function ($child) use ($item) {
                    return $child->id() === $item->id()
                        && Arr::get($child->manipulations(), 'action') !== '@alias';
                })
            );
        }
    }

    /**
     * Build sections collection.
     *
     * @return $this
     */
    protected function buildSections()
    {
        // Create sections...
        $sections = collect($this->sections)
            ->mapWithKeys(fn ($section) => [$section => []])
            ->all();

        // Organize items by section...
        collect($this->items)
            ->reject(fn ($item) => $item->isChild())
            ->reject(fn ($item) => $this->withHidden ? false : $item->isHidden())
            ->filter(fn ($item) => $item->section())
            ->each(function ($item) use (&$sections) {
                $sections[$item->section()][] = $item;
            });

        // Prepare section manipulations...
        $manipulations = collect($this->sectionsManipulations)
            ->keyBy('display')
            ->all();

        // Collect and order each section's items...
        $built = collect($sections)
            ->reject(fn ($items, $section) => $this->withHidden ? false : Arr::get($manipulations, "{$section}.action") === '@hide')
            ->filter(fn ($items) => $items || $this->withHidden)
            ->map(function ($items, $section) {
                return collect($this->sectionsWithReorderedItems)->contains($section)
                    ? collect($items)->sortBy(fn ($item) => $item->order())->values()
                    : collect($items);
            });

        // Transform sections to include section manipulations...
        $built->transform(function ($items, $section) use ($manipulations) {
            return [
                'display' => $section,
                'display_original' => $displayOriginal = Arr::get($manipulations, "{$section}.display_original") ?? $section,
                'action' => Arr::get($manipulations, "{$section}.action") ?? false,
                'items' => $items,
                'items_original' => Arr::get($this->sectionsOriginalItemIds, $displayOriginal),
            ];
        });

        // Order sections...
        if ($this->sectionsOrder) {
            $built = $built->sortBy(fn ($items, $section) => $this->sectionsOrder[$section]);
        }

        $this->built = $built->values();

        return $this;
    }

    /**
     * Use NavItem class to generate a new ID for item without registering it.
     *
     * @param  string  $section
     * @param  string  $name
     * @return string
     */
    protected function generateNewItemId($section, $name)
    {
        return (new NavItem)->display($name)->section($section)->id();
    }

    /**
     * Get built nav.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function get()
    {
        return $this->built;
    }
}
