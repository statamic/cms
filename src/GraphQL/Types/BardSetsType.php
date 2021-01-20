<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class BardSetsType extends ReplicatorSetsType
{
    public function resolveType($value)
    {
        return $value['type'] === 'text'
            ? GraphQL::type(BardTextType::buildName($this->fieldtype))
            : parent::resolveType($value);
    }
}
