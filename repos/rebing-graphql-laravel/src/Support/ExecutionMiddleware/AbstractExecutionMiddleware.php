<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\ExecutionMiddleware;

use Closure;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Type\Schema;
use Rebing\GraphQL\Support\OperationParams;

abstract class AbstractExecutionMiddleware
{
    /**
     * @param string $schemaName The name of the schema referenced from the config (`graphql.schema.<schemaName>`)
     *                           Note: it's possible that the provided `$schema` does not reflect the one provided by
     *                           the configuration (also referred to as "dynamic schema") and thus it's also not
     *                           guaranteed that the `$schemaName` actually exists.
     * @param Schema $schema The GraphQL schema; usually created via \Rebing\GraphQL\GraphQL::schema
     * @param OperationParams $params Encapsulating all the parameters for the GraphQL request
     * @param mixed $rootValue The initial value passed as the first argument to the query/mutation; if not set it's just `null`
     * @param mixed $contextValue The context passed to all resolvers; can be set to any value via your own execution middleware
     * @param Closure $next The next middleware to call
     */
    abstract public function handle(string $schemaName, Schema $schema, OperationParams $params, $rootValue, $contextValue, Closure $next): ExecutionResult;

    /**
     * @param array<string,mixed> $arguments
     */
    public function resolve(array $arguments, Closure $next): ExecutionResult
    {
        return $this->handle(...$arguments, ...[
            function (...$arguments) use ($next) {
                return $next($arguments);
            },
        ]);
    }
}
