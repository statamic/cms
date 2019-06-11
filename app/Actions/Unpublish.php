<?php

namespace Statamic\Actions;

use Statamic\API;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Entries\Entry;

class Unpublish extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'entries';
    }

    public function authorize($key, $context)
    {
        $collection = Collection::findByHandle($context['collection']);

        return user()->can('publish', [Entry::class, $collection]);
    }

    public function run($items)
    {
        $items->each(function ($entry) {
            $entry->unpublish(['user' => request()->user()]);
        });
    }
}
