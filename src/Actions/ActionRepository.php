<?php

namespace Statamic\Actions;

use Statamic\Facades\User;

class ActionRepository
{
    public function get($action)
    {
        if ($class = app('statamic.actions')->get($action)) {
            return app($class);
        }
    }

    public function all()
    {
        return app('statamic.actions')->map(function ($class) {
            return app($class);
        })->values();
    }

    public function for($item, $context = [])
    {
        return $this->all()
            ->each->items([$item])
            ->each->context($context)
            ->filter->visibleTo($item)
            ->filter->authorize(User::current(), $item)
            ->values();
    }

    public function forBulk($items, $context = [])
    {
        if ($items->count() === 1) {
            return $this->for($items->first(), $context);
        }

        return $this->all()
            ->each->items($items)
            ->each->context($context)
            ->filter->visibleToBulk($items)
            ->filter->authorizeBulk(User::current(), $items)
            ->values();
    }
}
