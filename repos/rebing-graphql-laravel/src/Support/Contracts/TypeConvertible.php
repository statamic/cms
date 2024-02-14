<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\Contracts;

use GraphQL\Type\Definition\Type;

interface TypeConvertible
{
    public function toType(): Type;
}
