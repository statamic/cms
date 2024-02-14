<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\AliasArguments;

use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;

class AliasArguments
{
    /** @var array<string,mixed> */
    private $queryArguments;
    /** @var array<string,mixed> */
    private $requestArguments;
    /** @var int */
    private $maxDepth;

    /**
     * @param array<string,mixed> $queryArguments
     * @param array<string,mixed> $requestArguments
     */
    public function __construct(array $queryArguments, array $requestArguments)
    {
        $this->queryArguments = $queryArguments;
        $this->requestArguments = $requestArguments;
        $this->maxDepth = $this->getArrayDepth($this->requestArguments);
    }

    public function get(): array
    {
        $pathsWithAlias = $this->getAliasesInFields($this->queryArguments);

        return (new ArrayKeyChange())->modify($this->requestArguments, $pathsWithAlias);
    }

    /**
     * c/p from https://stackoverflow.com/questions/262891/is-there-a-way-to-find-out-how-deep-a-php-array-is/262944#262944.
     *
     * @param array<string,mixed> $array
     */
    protected function getArrayDepth(array $array): int
    {
        $maxDepth = 1;

        foreach ($array as $value) {
            if (\is_array($value)) {
                $depth = $this->getArrayDepth($value) + 1;

                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }

        return $maxDepth;
    }

    protected function getAliasesInFields(array $fields, $prefix = ''): array
    {
        // checks for traversal beyond the max depth
        // this scenario occurs in types with recursive relations
        if (substr_count($prefix, '.') > $this->maxDepth) {
            return [];
        }
        $pathAndAlias = [];

        foreach ($fields as $name => $arg) {
            $type = null;

            // $arg is either an array DSL notation or an InputObjectField
            if ($arg instanceof InputObjectField) {
                $type = $arg->getType();
            } else {
                $arg = (object) $arg;
                $type = $arg->type ?? null;
            }

            if (null === $type) {
                continue;
            }

            $newPrefix = $prefix ? $prefix . '.' . $name : $name;

            $alias = $arg->config['alias'] ?? $arg->alias ?? null;

            if ($alias) {
                $pathAndAlias[$newPrefix] = $alias;
            }

            if ($this->isWrappedInList($type)) {
                $newPrefix .= '.*';
            }

            $type = $this->getWrappedType($type);

            if (!($type instanceof InputObjectType)) {
                continue;
            }

            $pathAndAlias = $pathAndAlias + $this->getAliasesInFields($type->getFields(), $newPrefix);
        }

        return $pathAndAlias;
    }

    private function isWrappedInList(Type $type): bool
    {
        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        return $type instanceof ListOfType;
    }

    private function getWrappedType(Type $type): Type
    {
        if ($type instanceof WrappingType) {
            $type = $type->getInnermostType();
        }

        return $type;
    }
}
