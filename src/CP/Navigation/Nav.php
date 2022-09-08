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
     * Create nav item.
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
     * Create nav item (an alias that reads a little nicer when creating children).
     *
     * @param  mixed  $name
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
            ->buildSections()
            ->getBuiltNav();
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
     * Build sections collection.
     *
     * @return $this
     */
    protected function buildSections()
    {
        $sections = [];

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

        $this->built = collect($sections)->map(function ($items) {
            return collect($items);
        });

        return $this;
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
            ->reject(function ($overrides) {
                return $overrides === '@inherit';
            })
            ->each(function ($overrides) {
                $this->applyPreferenceOverridesForSection($overrides);
            });

        collect($userNav['sections'])
            ->reject(function ($overrides, $section) {
                return $section === NavItem::snakecase($overrides['display']);
            })
            ->each(function ($overrides, $section) {
                $this->renameSection($section, $overrides['display']);
            });

        if ($userNav['reorder']) {
            // $this->reorderSections();
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
            // $this->reorderItems();
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
            ->each(fn ($item) => $item->section($displayNew));
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
     * Get built nav.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getBuiltNav()
    {
        return $this->built;
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
