<?php

namespace Statamic\API\Endpoint;

use Closure;
use Exception;
use Statamic\CP\Navigation\NavItem;

class Nav
{
    protected $type = 'extend';
    protected $vendor = [];
    protected $extend = [];

    /**
     * Create nav item.
     *
     * @param string $name
     * @return NavItem
     */
    public function item($name)
    {
        $item = (new NavItem)->name($name);

        $this->{$this->type}[] = $item;

        return $item;
    }

    /**
     * Add items to vendor array.
     *
     * @param Closure $callback
     */
    public function vendor(Closure $callback)
    {
        $this->type = 'vendor';

        $callback();

        $this->type = 'extend';
    }

    /**
     * Build sections for rendering.
     *
     * @return \Illuminate\Support\Collection
     */
    public function buildSections()
    {
        $this->validateNesting();
        $this->validateIcons();

        $sections = [];

        collect($this->items())
            ->filter(function ($item) {
                return $item->section();
            })
            ->each(function ($item) use (&$sections) {
                $sections[$item->section()] = $item;
            });

        return collect($sections);
    }

    /**
     * Get all items.
     *
     * @return array
     */
    protected function items()
    {
        return array_merge($this->vendor, $this->extend);
    }

    /**
     * Validate that nav children don't exceed nesting limit.
     *
     * @throws Exception
     */
    protected function validateNesting()
    {
        collect($this->items())
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
    }

    /**
     * Validate that nav children don't specify icons.
     *
     * @throws Exception
     */
    protected function validateIcons()
    {
        collect($this->items())
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
    }

    /**
     * Magically create nav items in sections by method name.
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

        return $this->item($arguments[0])->section($section);
    }
}
