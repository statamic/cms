<?php

namespace Statamic\API;

use Statamic\CP\Navigation\NavItem;

class Nav
{
    public static function item($name)
    {
        $item = new NavItem;
        $item->name($name);
        return $item;
    }
}
