<?php

namespace Statamic\CP\Navigation;

use Statamic\API\Str;

class NavItem
{
    private $name;
    private $title;
    private $url;
    private $icon;
    private $children;
    private $badge;

    public function __construct()
    {
        $this->children = new Nav;
    }

    public function name($name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;

        return $this;
    }

    public function title($title = null)
    {
        if (is_null($title)) {
            if ($this->title) {
                return $this->title;
            }

            return $this->name ? Str::title($this->name) : null;
        }

        $this->title = $title;

        return $this;
    }

    public function url($url = null)
    {
        if (is_null($url)) {
            return $this->url;
        }

        $this->url = $url;

        return $this;
    }

    public function route($route, $arg = null)
    {
        $url = ($arg) ? route($route, $arg) : route($route);

        return $this->url($url);
    }

    public function icon($icon = null)
    {
        if (is_null($icon)) {
            return $this->icon;
        }

        $this->icon = $icon;

        return $this;
    }

    public function badge($badge = null)
    {
        if (is_null($badge)) {
            return $this->badge;
        }

        $this->badge = $badge;

        return $this;
    }

    public function add($key, $item = null)
    {
        return $this->children->add($key, $item);
    }

    public function has($key)
    {
        return $this->children->has($key);
    }

    public function get($key)
    {
        return $this->children->get($key);
    }

    public function remove($key)
    {
        return $this->children->remove($key);
    }

    public function children()
    {
        return $this->children->children();
    }
}
