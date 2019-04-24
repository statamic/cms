<?php

namespace Statamic\Query\Scopes;

class Repository
{
    public function all()
    {
        return app('statamic.scopes')->map(function ($class) {
            return app($class);
        })->values();
    }

    public function for($key, $context = [])
    {
        return $this->all()
            ->each->context($context)
            ->filter->visibleTo($key)
            ->values();
    }
}
