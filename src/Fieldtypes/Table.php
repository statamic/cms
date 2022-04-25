<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Types\TableRowType;

class Table extends Fieldtype
{
    protected $categories = ['structured'];

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type(TableRowType::NAME));
    }
}
