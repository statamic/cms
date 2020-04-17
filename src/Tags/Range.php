<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Range extends Tags
{
    public function index()
    {
        $from   = $this->params->int('from', 1);
        $to     = $this->params->int(['to', 'times']);
        $vars = [];

        foreach(range($from, $to) as $i) {
            $vars[] = [
                'value' => $i,
            ];
        }

        return $vars;
    }
}
