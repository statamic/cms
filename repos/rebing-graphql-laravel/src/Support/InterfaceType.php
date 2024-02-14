<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use Closure;
use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;
use GraphQL\Type\Definition\Type as GraphqlType;

abstract class InterfaceType extends Type
{
    protected function getTypeResolver(): ?Closure
    {
        if (!method_exists($this, 'resolveType')) {
            return null;
        }

        $resolver = [$this, 'resolveType'];

        return function () use ($resolver) {
            $args = \func_get_args();

            return \call_user_func_array($resolver, $args);
        };
    }

    protected function getTypesResolver(): ?Closure
    {
        if (!method_exists($this, 'types')) {
            return null;
        }

        $resolver = [$this, 'types'];

        return function () use ($resolver): array {
            $args = \func_get_args();

            return \call_user_func_array($resolver, $args);
        };
    }

    /**
     * Get the attributes from the container.
     */
    public function getAttributes(): array
    {
        $attributes = parent::getAttributes();

        $resolverType = $this->getTypeResolver();

        if ($resolverType) {
            $attributes['resolveType'] = $resolverType;
        }

        $resolverTypes = $this->getTypesResolver();

        if ($resolverTypes) {
            $attributes['types'] = $resolverTypes;
        }

        return $attributes;
    }

    public function toType(): GraphqlType
    {
        return new BaseInterfaceType($this->toArray());
    }
}
