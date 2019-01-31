<?php

namespace Statamic\CP\Navigation;

use Closure;
use Exception;
use Statamic\API\Str;
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
    public function create($name)
    {
        $item = (new NavItem)->name($name);

        $this->items[] = $item;

        return $item;
    }

    /**
     * Create nav item (an alias that reads a little nicer when creating children).
     *
     * @param mixed $name
     */
    public function item($name)
    {
        return $this->create($name);
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

        return $item ?: $this->create($name)->section($section);
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
     * Get currently registered nav items as a raw array, before creating build structure.
     *
     * @return array
     */
    public function items()
    {
        return $this->items;
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
            ->validateIcons()
            ->validateViews()
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
                return is_callable($item->children()) && is_current($item->currentClass());
            })
            ->each(function ($item) {
                $item->children($item->children()());
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
                throw new Exception('Nav children have exceeded their nesting limit.');
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
                throw new Exception('These nav children cannot have icons.');
            });

        return $this;
    }

    /**
     * Validate that nav children don't specify views.
     *
     * @return $this
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
            ->reject(function ($item) {
                return $item->section() === 'Top Level'
                    && ! in_array($item->name(), DefaultNav::ALLOWED_TOP_LEVEL);
            })
            ->each(function ($item) use (&$sections) {
                $sections[$item->section()][] = $item;
            });

        return collect($sections)->map(function ($items) {
            return collect($items);
        });
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
