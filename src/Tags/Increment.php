<?php

namespace Statamic\Tags;

class Increment extends Tags
{
    protected static $arr = [];

    public function wildcard($tag)
    {
        if (! isset(self::$arr[$tag])) {
            return self::$arr[$tag] = $this->params->get('from', 0);
        }

        return self::$arr[$tag] = self::$arr[$tag] + $this->params->get('by', 1);
    }
}
