<?php

namespace Statamic\Actions;

use Statamic\API;

class Unpublish extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'entries';
    }

    public function run($items)
    {
        $items->each(function ($entry) {
            $entry->unpublish(['user' => request()->user()]);
        });
    }
}
