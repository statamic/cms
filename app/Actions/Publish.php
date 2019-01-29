<?php

namespace Statamic\Actions;

use Statamic\API;

class Publish extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'entries';
    }

    public function run($items)
    {
        $items->each(function ($entry) {
            $entry->publish()->save();
        });
    }
}
