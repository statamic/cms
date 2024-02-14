<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\ExecutionMiddleware;

use Closure;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Type\Schema;
use Illuminate\Contracts\Auth\Factory;
use Rebing\GraphQL\Support\OperationParams;

class AddAuthUserContextValueMiddleware extends AbstractExecutionMiddleware
{
    /** @var Factory */
    private $auth;

    public function __construct(Factory $auth)
    {
        $this->auth = $auth;
    }

    public function handle(string $schemaName, Schema $schema, OperationParams $params, $rootValue, $contextValue, Closure $next): ExecutionResult
    {
        if (null === $contextValue) {
            $contextValue = $this->auth->guard()->user();
        }

        return $next($schemaName, $schema, $params, $rootValue, $contextValue);
    }
}
