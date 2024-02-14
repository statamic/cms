<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\ExecutionMiddleware;

use Closure;
use GraphQL\Error\Error;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Type\Schema;
use Rebing\GraphQL\Support\OperationParams;

class UnusedVariablesMiddleware extends AbstractExecutionMiddleware
{
    public function handle(string $schemaName, Schema $schema, OperationParams $params, $rootValue, $contextValue, Closure $next): ExecutionResult
    {
        $unusedVariables = $params->variables;

        if (!$unusedVariables) {
            return $next($schemaName, $schema, $params, $rootValue, $contextValue);
        }

        $query = $params->getParsedQuery();

        foreach ($query->definitions as $definition) {
            if ($definition instanceof OperationDefinitionNode) {
                foreach ($definition->variableDefinitions as $variableDefinition) {
                    unset($unusedVariables[$variableDefinition->variable->name->value]);
                }
            }
        }

        if ($unusedVariables) {
            $msg = \Safe\sprintf(
                'The following variables were provided but not consumed: %s',
                implode(', ', array_keys($unusedVariables))
            );

            return new ExecutionResult(null, [new Error($msg)]);
        }

        return $next($schemaName, $schema, $params, $rootValue, $contextValue);
    }
}
