<?php

namespace Statamic\CP\Navigation;

use Closure;
use Statamic\API\Str;

class Nav
{
    public $tree;

    public function __construct()
    {
        $this->tree = collect();
    }

    public function add($item)
    {
        // If an non-item is passed in, fail.
        if (! $item instanceof NavItem && ! $item instanceof Closure) {
            throw new \Exception('An Item class or Closure is expected.');
        }

        if ($item instanceof Closure) {
            $item($this);
        } else {
            $this->tree->put($item->name(), $item);
        }

        return $this;
    }

    public function addTo($key, $item)
    {
        $this->tree->get($key)->add($item);
    }

    public function has($key)
    {
        if (! Str::contains($key, '.')) {
            return $this->tree->has($key);
        }

        $parts = explode('.', $key);

        $key = array_shift($parts);

        return $this->tree->get($key)->has(join('.', $parts));
    }

    public function get($key)
    {
        if (! Str::contains($key, '.')) {
            return $this->tree->get($key);
        }

        $parts = explode('.', $key);

        $key = array_shift($parts);

        return $this->tree->get($key)->get(join('.', $parts));
    }

    public function remove($key)
    {
        if ($key instanceof NavItem) {
            $key = $key->name();
        }

        if (! Str::contains($key, '.')) {
            return $this->tree->pull($key);
        }

        $parts = explode('.', $key);
        $removeKey =  array_pull($parts, count($parts)-1);

        return $this->get(join('.', $parts))->remove($removeKey);
    }

    public function children()
    {
        return $this->tree;
    }

    /**
     * Remove any sections that have no children.
     *
     * @return void
     */
    public function trim()
    {
        foreach ($this->children() as $item) {
            if ($item->children()->isEmpty()) {
                $this->remove($item);
            }
        }
    }
}
