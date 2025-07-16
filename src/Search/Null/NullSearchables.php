<?php

namespace Statamic\Search\Null;

use Illuminate\Support\LazyCollection;

class NullSearchables
{
    public function contains()
    {
        return false;
    }

    public function lazy(): LazyCollection
    {
        return LazyCollection::make();
    }
}
