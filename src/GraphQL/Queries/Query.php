<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\GraphQL\TypeRegistrar;
use Rebing\GraphQL\Support\Query as BaseQuery;

abstract class Query extends BaseQuery
{
    public function __construct()
    {
        TypeRegistrar::register();
    }
}
