<?php

namespace Statamic\Tags;

use Statamic\View\State\ResetsState;

class Increment extends Tags implements ResetsState
{
    protected static $arr = [];

    public static function resetStaticState()
    {
        self::$arr = [];
    }

    public function reset()
    {
        $counter = $this->params->get('counter', null);

        if ($counter == null) {
            return '';
        }

        $toValue = $this->params->get('to', null);

        if ($toValue == null) {
            unset(self::$arr[$counter]);
        } else {
            self::$arr[$counter] = $toValue;
        }

        if ($this->isPair) {
            return $this->parse();
        }

        return '';
    }

    public function wildcard($tag)
    {
        if (! isset(self::$arr[$tag])) {
            return self::$arr[$tag] = $this->params->get('from', 0);
        }

        return self::$arr[$tag] = self::$arr[$tag] + $this->params->get('by', 1);
    }
}
