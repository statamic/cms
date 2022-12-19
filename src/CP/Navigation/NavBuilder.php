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
            ->applyPreferenceOverrides($preferences)
            ->buildSections()
            ->get();
    }

    /**
     * Build children closures.
     *
     * @return $this
     */
    public function buildChildren()
    {
        collect($this->items)
            ->filter(fn ($item) => $item->isActive())
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
    public function syncOriginal()
    {
        collect($this->items)->each(fn ($item) => $item->syncOriginal());

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

        collect($navPreferencesConfig['sections'])
            ->each(fn ($overrides, $section) => $this->trackSectionManipulations($section, $overrides))
            ->reject(fn ($overrides, $section) => $section === NavItem::snakeCase($overrides['display']))
            ->each(fn ($overrides, $section) => $this->renameSection($section, $overrides['display']));

        collect($navPreferencesConfig['sections'])
            ->each(fn ($overrides) => $this->createPendingItemsForSection($overrides))
            ->each(fn ($overrides) => $this->applyPreferenceOverridesForSection($overrides));

        if ($navPreferencesConfig['reorder']) {
            $this->setSectionOrder($navPreferencesConfig['sections']);
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
                } elseif ($config['action'] === '@alias' || $config['action'] === '@move') {
                    return $key.'::clone';
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
            ->map(fn ($config, $id) => $this->applyPreferenceOverrideForItem($config, $section, $this->findItem($id)))
            ->filter()
            ->reject(fn ($item) => $item->manipulations()['action'] === '@modify')
            ->each(fn ($item) => $this->items[] = $item);

        if ($sectionNav['reorder']) {
            $this->setSectionItemOrder($section, $sectionNav['items']);
        }
    }

    /**
     * Apply preference overide for specific item.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  NavItem|null  $item
     * @param  string|null  $id
     * @return NavItem|null
     */
    protected function applyPreferenceOverrideForItem($config, $section, $item = null, $id = null)
    {
        switch ($config['action']) {
            case '@create':
                return $this->userCreateFromPendingItem($config, $section, $id);
            case '@hide':
                return $this->userHideItem($item);
            case '@modify':
                return $this->userModifyItem($item, $config, $section);
            case '@alias':
                return $this->userAliasItem($item, $config, $section);
            case '@move':
                return $this->userMoveItem($item, $config, $section);
        }
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
     * Rename section.
     *
     * @param  string  $sectionKey
     * @param  string  $displayNew
     */
    protected function renameSection($sectionKey, $displayNew)
    {
        $displayOriginal = null;

        collect($this->items)
            ->filter(fn ($item) => NavItem::snakeCase($item->section()) === $sectionKey)
            ->each(function ($item) use (&$displayOriginal, $displayNew) {
                $displayOriginal = $item->section();
                $item->preserveCurrentId()->section($displayNew);
            });

        $this->sectionsManipulations[$sectionKey]['display_original'] = $displayOriginal;
    }

    /**
     * Set section order.
     *
     * @param  array  $sections
     */
    protected function setSectionOrder($sections)
    {
        // Get unconfigured sections...
        $unconfiguredSections = collect($this->items)->map->section()->filter()->unique();

        // Merge unconfigured sections onto the end of the list and map their order...
        $this->sectionsOrder = collect($sections)
            ->pluck('display')
            ->merge($unconfiguredSections)
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
        $itemIds = collect($items);

        // Generate IDs for newly created items...
        $itemIds->transform(function ($item, $id) use ($section, $items) {
            return $items[$id]['action'] === '@create'
                ? $this->generateNewItemId($section, $items[$id]['display'])
                : $item;
        });

        // Items that are moved or aliased into this section should have `::clone` appended to their IDs...
        $itemIds->transform(function ($item, $id) use ($items) {
            return in_array($items[$id]['action'], ['@move', '@alias'])
                ? $id.'::clone'
                : $item;
        });

        // Ensure the rest of the items are transformed to IDs...
        $itemIds->transform(fn ($item, $id) => is_array($item) ? $id : $item);

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
            ->map(fn ($id) => $this->findItem($id))
            ->filter()
            ->each(fn ($item, $index) => $item->order($index + 1));

        // Inform builder that section items should be ordered...
        $this->sectionsWithReorderedItems[] = $section;
    }

    /**
     * Find existing nav item by ID.
     *
     * @param  string  $id
     * @return NavItem|null
     */
    protected function findItem($id)
    {
        $pendingItems = collect($this->pendingItems);

        if ($item = $pendingItems->get($id)) {
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
     * @return NavItem|null
     */
    protected function findParentItem($id)
    {
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
     * @return NavItem
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

        $this->userModifyItem($item, $config, $section);

        $this->pendingItems[$item->id()] = $item;

        return $item;
    }

    /**
     * Create new NavItem from pending created item.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  string  $id
     */
    protected function userCreateFromPendingItem($config, $section, $id = null)
    {
        $config = collect($config);

        if (! $display = $config->get('display')) {
            return;
        }

        $id = $id ?? $this->generateNewItemId($section, $display);

        if (! $pendingItem = collect($this->pendingItems)->get($id)) {
            return;
        }

        return $pendingItem;
    }

    /**
     * Hide NavItem.
     *
     * @param  NavItem  $item
     */
    protected function userHideItem($item)
    {
        if (is_null($item)) {
            return;
        }

        $item->manipulations(['action' => '@hide']);

        if ($this->withHidden) {
            return $item;
        }

        $item->hidden(true);

        $this->userRemoveItemFromChildren($item);
    }

    /**
     * Modify NavItem.
     *
     * @param  NavItem  $item
     * @param  array  $config
     * @param  string  $section
     */
    protected function userModifyItem($item, $config, $section)
    {
        if (is_null($item)) {
            return;
        }

        $item->preserveCurrentId();

        $item->manipulations($config);

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
     * @param  NavItem  $item
     * @param  array  $childrenOverrides
     * @param  string  $section
     * @return \Illuminate\Support\Collection
     */
    protected function userModifyItemChildren($item, $childrenOverrides, $section, $reorder)
    {
        $itemChildren = collect($item->resolveChildren()->children())
            ->each(fn ($item, $index) => $item->order('_'.$index + 1))
            ->keyBy
            ->id();

        collect($childrenOverrides)
            ->keyBy(fn ($config, $key) => $this->normalizeChildId($item, $key))
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

        $item->children($newChildren, false);

        return $newChildren;
    }

    /**
     * Modify child NavItem.
     *
     * @param  array  $config
     * @param  string  $section
     * @param  string  $key
     * @param  NavItem  $parentItem
     * @return mixed
     */
    protected function userModifyChild($config, $section, $key, $parentItem)
    {
        $item = $this->findItem($key);

        if ($config['action'] === '@inherit') {
            return $item;
        }

        if ($config['action'] === '@create' && isset($config['display'])) {
            $id = $this->generateNewItemId($parentItem->id(), $config['display']);
        }

        if ($childItem = $this->applyPreferenceOverrideForItem($config, $section, $item, $id ?? null)) {
            $childItem->children([]);
        }

        return $childItem;
    }

    /**
     * Create alias for NavItem.
     *
     * @param  NavItem  $item
     * @param  array  $config
     * @param  string  $section
     * @param  bool  $setChildren
     */
    protected function userAliasItem($item, $config, $section, $setChildren = true)
    {
        if (is_null($item)) {
            return;
        }

        $clone = clone $item;

        $clone->id($clone->id().'::clone');

        $clone->section($section);

        $this->userModifyItem($clone, $config, $section);

        if ($setChildren) {
            $this->setChildrenOnAliasedItem($clone, $config, $section);
        }

        return $clone;
    }

    /**
     * Move NavItem to new section.
     *
     * @param  NavItem  $item
     * @param  array  $config
     * @param  string  $section
     */
    protected function userMoveItem($item, $config, $section)
    {
        if (is_null($item)) {
            return;
        }

        $clone = $this->userAliasItem($item, $config, $section, false);

        $this->userRemoveItem($item);
        $this->userRemoveItemFromChildren($item);

        return $clone;
    }

    /**
     * Remove NavItem completely, so that it's not even visible when the `$withHidden` flag is set.
     *
     * @param  mixed  $item
     */
    protected function userRemoveItem($item)
    {
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
        if ($parent = $this->findParentItem($item->id())) {
            $parent->resolveChildren()->children(
                $parent->children()->reject(fn ($child) => $child->id() === $item->id())
            );
        }
    }

    /**
     * Set children on aliased item.
     *
     * @param  NavItem  $item
     * @param  array  $config
     * @param  string  $section
     */
    protected function setChildrenOnAliasedItem($item, $config, $section)
    {
        $children = collect(Arr::get($config, 'children', []))
            ->map(function ($childConfig, $id) use ($item, $section) {
                return $this->applyPreferenceOverrideForItem(
                    $childConfig,
                    $section,
                    $this->findItem($id),
                    $childConfig['action'] === '@create' ? $this->generateNewItemId($item->id(), $childConfig['display']) : null
                );
            })
            ->filter()
            ->all();

        $item->children($children);
    }

    /**
     * Build sections collection.
     *
     * @return $this
     */
    protected function buildSections()
    {
        $sections = [];

        // Organize items by section...
        collect($this->items)
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
            ->map(function ($items, $section) {
                return collect($this->sectionsWithReorderedItems)->contains($section)
                    ? collect($items)->sortBy(fn ($item) => $item->order())->values()
                    : collect($items);
            });

        // Transform sections to include section manipulations...
        $built->transform(function ($items, $section) use ($manipulations) {
            return [
                'display' => $section,
                'display_original' => Arr::get($manipulations, "{$section}.display_original") ?? $section,
                'action' => Arr::get($manipulations, "{$section}.action") ?? false,
                'items' => $items,
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
     * Normalize child ID when parent has been cloned.
     *
     * @param  NavItem  $parentItem
     * @param  string  $childKey
     * @return string
     */
    protected function normalizeChildId($parentItem, $childKey)
    {
        if (Str::endsWith($parentItem->id(), '::clone')) {
            $parts = collect(explode('::', $childKey));
            $last = $parts->pop();
            $childKey = $parts->push('clone')->push($last)->implode('::');
        }

        return $childKey;
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
