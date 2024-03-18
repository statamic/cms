<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Contracts\TypeConvertible;

class ArrayType extends ScalarType implements TypeConvertible
{
    const NAME = 'Array';

    public function serialize($value)
    {
        return $value;
    }

    public function parseValue($value)
    {
        return $value;
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        return $valueNode->value;
    }

    public function toType(): Type
    {
        return new static();
    }
}
