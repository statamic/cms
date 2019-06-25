<?php

namespace Statamic\Actions;

use Illuminate\Support\Collection;

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

    public function for($key, $context = [], $items = null)
    {
        $actions = $this->all()
            ->filter->visibleTo($key, $context)
            ->each->context($context)
            ->values();

        if ($items) {
            return $this->filterAuthorized($actions, $items)->values();
        }

        return $actions;
    }

    protected function filterAuthorized($actions, $item)
    {
        if (! $item instanceof Collection) {
            return $actions->filter->authorize($item);
        }

        $items = $item;

        return $actions->filter(function ($action) use ($items) {
            return $this->canActionBeRunOnAllItems($action, $items);
        });
    }

    protected function canActionBeRunOnAllItems($action, $items)
    {
        $authorized = $items->filter(function ($item) use ($action) {
            return $action->authorize($item);
        });

        return $authorized->count() === $items->count();
    }
}
