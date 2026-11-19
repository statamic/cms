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

    public function index()
    {
        $counter = $this->params->get('counter', null);

        return $this->increment($counter);
    }

    public function wildcard($counter)
    {
        return $this->increment($counter);
    }

    protected function increment($counter)
    {
        if (! isset(self::$arr[$counter])) {
            return self::$arr[$counter] = $this->params->get('from', 0);
        }

        return self::$arr[$counter] = self::$arr[$counter] + $this->params->get('by', 1);
    }
}
