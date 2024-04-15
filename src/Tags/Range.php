<?php

namespace Statamic\Tags;

class Range extends Tags
{
    protected static $aliases = ['loop'];

    public function index()
    {
        $from = $this->params->int('from', 1);
        $to = $this->params->int(['to', 'times']);
        $vars = [];

        if ($to === 0 && ! $this->params->has('from')) {
            return [];
        }

        foreach (range($from, $to) as $i) {
            $vars[] = [
                'value' => $i,
            ];
        }

        return $vars;
    }
}
