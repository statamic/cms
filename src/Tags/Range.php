<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Range extends Tags
{
    public function index()
    {
        $from   = $this->getInt('from', 1);
        $to     = $this->getInt('to', null);
        $times  = $this->getInt('times', null);
        $vars = [];

        if ($times) {
            $to = $times;
        }

        foreach(range($from, $to) as $i) {
            $vars[] = [
                'value' => $i,
            ];
        }

        return $this->parseLoop($vars);
    }
}
