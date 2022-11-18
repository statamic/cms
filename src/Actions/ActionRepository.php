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
        $context = $context + ['view' => 'listing'];

        return $this->all()
            ->each->context($context)
            ->filter->visibleTo($item)
            ->filter->authorize(User::current(), $item)
            ->values();
    }

    public function forBulk($items, $context = [])
    {
        $context = $context + ['view' => 'listing'];

        return $this->all()
            ->each->context($context)
            ->filter->visibleToBulk($items)
            ->filter->authorizeBulk(User::current(), $items)
            ->values();
    }
}
