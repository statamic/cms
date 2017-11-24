<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\SiteHelpers\Filters as SiteFilters;

class FilterLoader
{
    public function load($name, $properties)
    {
        $name = Str::studly($name);

        if (class_exists(SiteFilters::class) && method_exists(SiteFilters::class, $name)) {
            return $this->init(SiteFilters::class, $properties);
        }

        return $this->init("Statamic\\Addons\\{$name}\\{$name}Filter", $properties);
    }

    private function init($class, $properties)
    {
        return tap(app($class), function ($filter) use ($properties) {
            $filter->setProperties($properties);
        });
    }
}
