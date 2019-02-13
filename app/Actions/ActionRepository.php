<?php

namespace Statamic\Actions;

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

    public function for($key, $context = [])
    {
        return $this->all()
            ->filter->visibleTo($key, $context)
            ->each->context($context)
            ->values();
    }
}
