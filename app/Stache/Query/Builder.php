<?php

namespace Statamic\Stache\Query;

use Statamic\Data\QueryBuilder;
use Statamic\Stache\Stores\Store;

abstract class Builder extends QueryBuilder
{
    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }
}