<?php

namespace Statamic\Filters;

class FilterRepository
{
    public function all()
    {
        return app('statamic.filters')->map(function ($class) {
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
