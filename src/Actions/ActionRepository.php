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
            ->each->context($context)
            ->filter->filter($item)
            ->filter->authorize(User::current(), $item)
            ->values();
    }
}
