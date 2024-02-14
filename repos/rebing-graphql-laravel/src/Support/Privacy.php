<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

abstract class Privacy
{
    public function fire(...$args): bool
    {
        return $this->validate(...$args);
    }

    /**
     * @param array<string, mixed> $queryArgs Arguments given with the query/mutation
     * @param mixed $queryContext Context of the query/mutation
     *
     * @return bool Return `true` to allow access to the field in question,
     *              `false otherwise
     */
    abstract public function validate(array $queryArgs, $queryContext = null): bool;
}
