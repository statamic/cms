<?php

namespace Statamic\API\Endpoint;

use Closure;
use Exception;
use Statamic\CP\Navigation\NavItem;
use Statamic\CP\Navigation\DefaultNav;

class Nav
{
    protected $items = [];
    protected $extensions = [];

    /**
     * Register a nav extension closure.
     *
     * @param Closure $callback
     */
    public function extend(Closure $callback)
    {
        $this->extensions[] = $callback;
    }

    /**
     * Create nav item.
     *
     * @param string $name
     * @return NavItem
     */
    public function item($name)
    {
        $item = (new NavItem)->name($name);

        $this->items[] = $item;

        return $item;
    }

    /**
     * Find or create nav item.
     *
     * @param string $section
     * @param string $name
     */
    public function findOrCreate($section, $name)
    {
        $item = collect($this->items)->first(function ($item) use ($section, $name) {
            return $item->section() === $section
                && $item->name() === $name;
        });

        return $item ?: $this->item($name)->section($section);
    }

    /**
     * Remove nav item.
     *
     * @param string $section
     * @param string|null $name
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
     * Build navigation.
     *
     * @return \Illuminate\Support\Collection
     */
    public function build()
    {
        return $this
            ->makeDefaultItems()
            ->runExtensions()
            ->validateNesting()
            ->validateIcons()
            ->filterAuthorized()
            ->buildSections();
    }

    /**
     * Make default nav items.
     *
     * @return $this
     */
    protected function makeDefaultItems()
    {
        DefaultNav::make();

        return $this;
    }

    /**
     * Run extension closures.
     *
     * @return $this
     */
    protected function runExtensions()
    {
        collect($this->extensions)->each(function ($callback) {
            $callback($this);
        });

        return $this;
    }

    /**
     * Validate that nav children don't exceed nesting limit.
     *
     * @return $this
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
                // TODO: Write more serious exception.
                throw new Exception('Nav children cannot biologically have more nav children.');
            });

        return $this;
    }

    /**
     * Validate that nav children don't specify icons.
     *
     * @return $this
     * @throws Exception
     */
    protected function validateIcons()
    {
        collect($this->items)
            ->flatMap(function ($item) {
                return $item->children();
            })
            ->reject(function ($item) {
                return is_null($item->icon());
            })
            ->each(function ($item) {
                // TODO: Write more serious exception.
                throw new Exception('Nav children cannot be iconic.');
            });

        return $this;
    }

    /**
     * Filter authorized nav items.
     *
     * @return $this
     */
    protected function filterAuthorized()
    {
        $this->items = collect($this->items)
            ->filter(function ($item) {
                return $item->authorization()
                    // TODO: Ensure this actually works.
                    ? auth()->user()->can($item->can()->ability, $item->can()->arguments)
                    : true;
            });

        return $this;
    }

    /**
     * Build sections collection.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function buildSections()
    {
        $sections = [];

        collect($this->items)
            ->filter(function ($item) {
                return $item->section();
            })
            ->each(function ($item) use (&$sections) {
                $sections[$item->section()][] = $item;
            });

        return collect($sections);
    }

    /**
     * Magically find or create nav items, specifying the section name in sections by method name.
     *
     * @param string $name
     * @param array $arguments
     * @return NavItem
     */
    public function __call($name, $arguments)
    {
        // Convert camel case name method name to title case section name.
        $section = Str::modifyMultiple($name, ['snake', 'title', function ($string) {
            return str_replace('_', ' ', $string);
        }]);

        return $this->findOrCreate($section, $arguments[0]);
    }
}
