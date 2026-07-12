<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class BardSetsType extends ReplicatorSetsType
{
    public function resolveType($value)
    {
        return $value['type'] === 'text'
            ? GraphQL::type(BardTextType::NAME)
            : parent::resolveType($value);
    }
}
