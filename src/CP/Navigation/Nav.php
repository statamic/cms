<?php

namespace Statamic\CP\Navigation;

use Closure;
use Exception;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Statamic\Support\Str;

class Nav
{
    protected $items = [];
    protected $extensions = [];
    protected $built;
    protected $withHidden = false;
    protected $sectionsOrder = [];
    protected $sectionsWithReorderedItems = [];

    /**
     * Register a nav extension closure.
     *
     * @param  Closure  $callback
     */
    public function extend(Closure $callback)
    {
        $this->extensions[] = $callback;
    }

    /**
     * Create and register nav item.
     *
     * @param  string  $name
     * @return NavItem
     */
    public function create($name)
    {
        $item = (new NavItem)->display($name);

        $this->items[] = $item;

        return $item;
    }

    /**
     * Create and register nav item (an alias that reads a little nicer when creating children).
     *
     * @param  string  $name
     * @return NavItem
     */
    public function item($name)
    {
        return $this->create($name);
    }

    /**
     * Find or create nav item.
     *
     * @param  string  $section
     * @param  string  $name
     */
    public function findOrCreate($section, $name)
    {
        $item = collect($this->items)->first(function ($item) use ($section, $name) {
            return $item->section() === $section
                && $item->display() === $name;
        });

        return $item ?: $this->create($name)->section($section);
    }

    /**
     * Remove nav item.
     *
     * @param  string  $section
     * @param  string|null  $name
     * @return $this
     */
    public function remove($section, $name = null)
    {
        $this->items = collect($this->items)
            ->reject(function ($item) use ($section, $name) {
                return $name
                    ? $item->section() === $section && $item->display() === $name
                    : $item->section() === $section;
            })
            ->all();

        return $this;
    }

    /**
     * Get currently registered nav items as a raw array, before creating build structure.
     *
     * @return array
     */
    public function items()
    {
        return $this->items; // TODO: sometimes this is a closure though?
    }

    /**
     * Include hidden items for when customizing nav.
     *
     * @return $this
     */
    public function withHidden()
    {
        $clone = clone $this;

        $clone->withHidden = true;

        return $clone;
    }

    /**
     * Build navigation.
     *
     * @return \Illuminate\Support\Collection
     */
    public function build()
    {
        return $this
            ->makeDefaultItems()
            ->buildExtensions()
            ->buildChildren()
            ->validateNesting()
            ->validateViews()
            ->authorizeItems()
            ->authorizeChildren()
            ->applyPreferenceOverrides()
            ->buildSections();
    }

    /**
     * Make default nav items.
     *
     * @return $this
     */
    protected function makeDefaultItems()
    {
        CoreNav::make();

        return $this;
    }

    /**
     * Build extension closures.
     *
     * @return $this
     */
    protected function buildExtensions()
    {
        collect($this->extensions)->each(function ($callback) {
            $callback($this);
        });

        return $this;
    }

    /**
     * Build children closures.
     *
     * @return $this
     */
    public function buildChildren()
    {
        collect($this->items)
            ->filter(function ($item) {
                return $item->isActive();
            })
            ->each(function ($item) {
                $item->resolveChildren();
            });

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
            ->flatMap(function ($item) {
                return $item->children();
            })
            ->reject(function ($item) {
                return empty($item->children());
            })
            ->each(function ($item) {
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
            ->flatMap(function ($item) {
                return $item->children();
            })
            ->reject(function ($item) {
                return is_null($item->view());
            })
            ->each(function ($item) {
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
            ->reject(function ($item) {
                return is_callable($item->children());
            })
            ->each(function ($item) {
                $item->children($this->filterAuthorizedNavItems($item->children()));
            });

        return $this;
    }

    /**
     * Filter authorized nav items.
     *
     * @param  mixed  $items
     * @return \Illuminate\Support\Collection
     */
    protected function filterAuthorizedNavItems($items)
    {
        return collect($items)
            ->filter(function ($item) {
                return $item->authorization()
                    ? User::current()->can($item->can()->ability, $item->can()->arguments)
                    : true;
            })
            ->values();
    }

    /**
     * Apply overrides from user preferences.
     *
     * @return $this
     */
    protected function applyPreferenceOverrides()
    {
        if (! $userNav = Preference::get('nav')) {
            return $this;
        }

        $userNav = UserNavConfig::normalize($userNav);

        collect($userNav['sections'])
            ->reject(function ($overrides, $section) {
                return $section === NavItem::snakeCase($overrides['display']);
            })
            ->each(function ($overrides, $section) {
                $this->renameSection($section, $overrides['display']);
            });

        collect($userNav['sections'])
            ->reject(function ($overrides) {
                return $overrides === '@inherit';
            })
            ->each(function ($overrides) {
                $this->applyPreferenceOverridesForSection($overrides);
            });

        if ($userNav['reorder']) {
            $this->setSectionOrder($userNav['sections']);
        }

        return $this;
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
            ->map(function ($config, $id) {
                return [
                    'item' => $this->findItem($id),
                    'config' => $config,
                ];
            })
            ->each(function ($override) use ($section) {
                switch ($override['config']['action']) {
                    case '@create':
                        return $this->userCreateItem($override['config'], $section);
                    case '@remove':
                        return $this->userRemoveItem($override['item']);
                    case '@modify':
                        return $this->userModifyItem($override['item'], $override['config'], $section);
                    case '@alias':
                        return $this->userAliasItem($override['item'], $override['config'], $section);
                    case '@move':
                        return $this->userMoveItem($override['item'], $override['config'], $section);
                }
            });

        if ($sectionNav['reorder']) {
            $this->setSectionItemOrder($section, $sectionNav['items']);
        }
    }

    /**
     * Rename section.
     *
     * @param  string  $sectionKey
     * @param  string  $displayNew
     */
    protected function renameSection($sectionKey, $displayNew)
    {
        $this->items
            ->filter(fn ($item) => NavItem::snakeCase($item->section()) === $sectionKey)
            ->each(function ($item) use ($displayNew) {
                $item
                    ->id($item->id()) // Preserve the item's original ID before setting the section.
                    ->section($displayNew);
            });
    }

    /**
     * Set section order.
     *
     * @param  array  $sections
     */
    protected function setSectionOrder($sections)
    {
        $this->sectionsOrder = collect($sections)
            ->pluck('display')
            ->merge($this->items->map->section()->filter()->unique())
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

        // Merge any unconfigured section items into the end of the list...
        $itemIds = $itemIds
            ->values()
            ->merge($this->items->filter(fn ($item) => $item->section() === $section)->map->id())
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
        $items = $this->items->keyBy->id();

        if ($item = $items->get($id)) {
            return $item;
        }

        if ($parent = $this->findParentItem($id)) {
            $parent->resolveChildren();
            $parent->children()->each(function ($item) use ($items) {
                $items->put($item->id(), $item);
            });
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
        $items = $this->items->keyBy->id();

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
     */
    protected function userCreateItem($config, $section)
    {
        $config = collect($config);

        if (! $display = $config->get('display')) {
            return;
        }

        $item = $this->create($display)->section($section);

        $this->userModifyItem($item, $config);
    }

    /**
     * Remove NavItem.
     *
     * @param  NavItem  $item
     */
    protected function userRemoveItem($item)
    {
        if (is_null($item)) {
            return;
        }

        $item->hidden(true);

        $this->userRemoveItemFromChildren($item);
    }

    /**
     * Modify NavItem.
     *
     * @param  NavItem  $item
     * @param  array  $config
     */
    protected function userModifyItem($item, $config)
    {
        if (is_null($item)) {
            return;
        }

        $config = collect($config);

        collect(UserNavConfig::ALLOWED_NAV_ITEM_MODIFICATIONS)
            ->filter(fn ($setter) => $config->has($setter))
            ->each(fn ($setter) => $item->{$setter}($config->get($setter)));
    }

    /**
     * Create alias for NavItem.
     *
     * @param  NavItem  $item
     * @param  array  $config
     * @param  string  $section
     */
    protected function userAliasItem($item, $config, $section)
    {
        if (is_null($item)) {
            return;
        }

        $clone = clone $item;

        $clone->id($clone->id().'::clone');

        $clone->section($section);

        $this->userModifyItem($clone, $config);

        $this->items[] = $clone;
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

        $this->userAliasItem($item, $config, $section);

        $item->hidden(true);

        $this->userRemoveItemFromChildren($item);
    }

    /**
     * Remove NavItem from parent's children.
     *
     * @param  mixed  $item
     */
    protected function userRemoveItemFromChildren($item)
    {
        if ($parent = $this->findParentItem($item->id())) {
            $parent->children(
                $parent->children()->reject(function ($child) use ($item) {
                    return $child->id() === $item->id();
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
        $sections = [];

        // Organize items by section...
        collect($this->items)
            ->reject(function ($item) {
                return $this->withHidden ? false : $item->isHidden();
            })
            ->filter(function ($item) {
                return $item->section();
            })
            ->each(function ($item) use (&$sections) {
                $sections[$item->section()][] = $item;
            });

        // Collect and order each section's items...
        $built = collect($sections)
            ->map(function ($items, $section) {
                return collect($this->sectionsWithReorderedItems)->contains($section)
                    ? collect($items)->sortBy(fn ($item) => $item->order())
                    : collect($items);
            });

        // Order sections...
        if ($this->sectionsOrder) {
            return $built->sortBy(fn ($items, $section) => $this->sectionsOrder[$section]);
        }

        return $built;
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
     * Magically find or create nav items, specifying the section name in sections by method name.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return NavItem
     */
    public function __call($name, $arguments)
    {
        return $this->findOrCreate(Str::studlyToTitle($name), $arguments[0]);
    }
}
