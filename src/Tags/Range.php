<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Range extends Tags
{
    public function index()
    {
        $from   = $this->params->int('from', 1);
        $to     = $this->params->int('to', null);
        $times  = $this->params->int('times', null);
        $vars = [];

        if ($times) {
            $to = $times;
        }

        foreach(range($from, $to) as $i) {
            $vars[] = [
                'value' => $i,
            ];
        }

        return $vars;
    }
}
