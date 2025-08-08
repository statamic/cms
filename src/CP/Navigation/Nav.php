<?php

namespace Statamic\CP\Navigation;

use Closure;
use Statamic\Support\Str;

class Nav
{
    protected $items = [];
    protected $extensions = [];

    /**
     * Register a nav extension closure.
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
     * Find nav item.
     *
     * @param  string  $section
     * @param  string  $name
     * @return NavItem|null
     */
    public function find($section, $name)
    {
        $item = collect($this->items)->first(function ($item) use ($section, $name) {
            return $item->section() === $section
                && $item->display() === $name
                && ! $item->isChild();
        });

        return $item;
    }

    /**
     * Find or create nav item.
     *
     * @param  string  $section
     * @param  string  $name
     * @return NavItem
     */
    public function findOrCreate($section, $name)
    {
        return $this->find($section, $name) ?: $this->create($name)->section($section);
    }

    /**
     * Remove nav item.
     *
     * @param  string  $section
     * @param  string|null  $name
     * @param  string|null  $childName
     * @return $this
     */
    public function remove($section, $name = null, $childName = null)
    {
        if ($childName) {
            return $this->removeChildItem($section, $name, $childName);
        }

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
     * Remove nav item.
     *
     * @param  string  $section
     * @param  string|null  $name
     * @param  string|null  $childName
     * @return $this
     */
    protected function removeChildItem($section, $name, $childName)
    {
        if (! $parent = $this->find($section, $name)) {
            return $this;
        }

        if (! $children = $parent->resolveChildren()->children()) {
            return $this;
        }

        $parent->children($children->reject(fn ($child) => $child->display() === $childName));

        return $this;
    }

    /**
     * Build navigation.
     *
     * @param  mixed  $preferences
     * @return \Illuminate\Support\Collection
     */
    public function build($preferences = true, bool $editing = false)
    {
        return (new NavBuilder($this->makeBaseItems()))
            ->withHidden($editing)
            ->build($preferences);
    }

    /**
     * Clear cached urls.
     */
    public function clearCachedUrls()
    {
        return NavBuilder::clearCachedUrls();
    }

    /**
     * Make base items.
     *
     * @return $this
     */
    protected function makeBaseItems()
    {
        $originalItems = $this->items;

        CoreNav::make();

        collect($this->extensions)->each(function ($callback) {
            $callback($this);
        });

        $items = $this->items;

        $this->items = $originalItems;

        return $items;
    }

    /**
     * Get currently registered items.
     *
     * @return array
     */
    public function items()
    {
        return $this->items;
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
