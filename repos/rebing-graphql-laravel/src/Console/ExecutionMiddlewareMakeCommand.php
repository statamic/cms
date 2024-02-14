<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:executionMiddleware')]
class ExecutionMiddlewareMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:executionMiddleware {name}';
    protected $description = 'Create a new GraphQL execution middleware class';
    protected $type = 'ExecutionMiddleware';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/executionMiddleware.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Middleware\Execution';
    }
}
