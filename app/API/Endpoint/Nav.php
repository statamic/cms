<?php

namespace Statamic\API\Endpoint;

use Statamic\CP\Navigation\NavItem;

class Nav
{
    public function item($name)
    {
        $item = new NavItem;
        $item->name($name);
        return $item;
    }
}
