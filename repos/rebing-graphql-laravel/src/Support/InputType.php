<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type as GraphqlType;

abstract class InputType extends Type
{
    public function toType(): GraphqlType
    {
        return new InputObjectType($this->toArray());
    }
}
