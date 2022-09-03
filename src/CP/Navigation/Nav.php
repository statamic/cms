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
        $item = (new NavItem)->name($name);

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
                && $item->name() === $name;
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
                    ? $item->section() === $section && $item->name() === $name
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
            // ->reject(function ($item) {
            //     return $item->section() === 'Top Level'
            //         && ! in_array($item->name(), CoreNav::ALLOWED_TOP_LEVEL);
            // })
            ->each(function ($item) use (&$sections) {
                $sections[$item->section()][] = $item;
            });

        $this->built = collect($sections)->map(function ($items) {
            return collect($items);
        });

        return $this;
    }

    protected function applyPreferenceOverrides()
    {
        if (! $userNav = Preference::get('nav')) {
            return $this;
        }

        collect($userNav)
            ->map(function ($overrides, $section) {
                return $this->normalizeOverrides($overrides, $section);
            })
            ->each(function ($overrides, $section) {
                $this->applyPreferenceOverridesForSection($overrides, $section);
            });

        return $this;
    }

    protected function normalizeOverrides($overrides, $section)
    {
        return collect($overrides)->map(function ($item) use ($section) {
            return is_string($item) && ! Str::contains($item, '::')
                ? "{$section}::{$item}"
                : $item;
        });
    }

    protected function applyPreferenceOverridesForSection($overrides, $section)
    {
        $overrides
            ->map(function ($item) {
                return $this->findOrCreateItem($item);
            })
            ->each(function ($item) use ($section) {
                $item->section(Str::modifyMultiple($section, ['deslugify', 'title']));
            });
    }

    protected function findOrCreateItem($id)
    {
        // cache this to class so we can keep building on it, or key them on class by id?
        $items = $this->items->keyBy->id();

        $options = collect(explode('@', $id));
        $id = $options->shift();

        $item = $items->get($id);

        $idParts = collect(explode('::', $id));

        if ($idParts->count() > 2) {
            $parentId = $idParts[0].'::'.$idParts[1];
        }

        $parent = isset($parentId) ? $items->get($parentId) : null;

        if ($parent && ! $item) {
            $parent->resolveChildren();
            $parent->children()->each(function ($item) use ($items) {
                $items->put($item->id(), $item);
            });
            $item = $items->get($id);
        }

        if ($item) {
            $cloned = clone $item;
            $this->items[] = $cloned;

            if ($options->contains('move')) {
                $item->hidden(true);
            }

            if ($parent && $options->contains('move')) {
                $parent->children(
                    $parent->children()->reject(function ($item) use ($id) {
                        return $id === $item->id();
                    })
                );
            }

            return $cloned;
        }

        return $this->create('WOTTT');
    }

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
