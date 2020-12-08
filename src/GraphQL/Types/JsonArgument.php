<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Type as GraphqlType;
use Rebing\GraphQL\Support\Type;

class JsonArgument extends Type
{
    const NAME = 'JsonArgument';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function toType(): GraphqlType
    {
        return new CustomScalarType($this->toArray());
    }
}
