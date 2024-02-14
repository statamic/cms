<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:middleware')]
class MiddlewareMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:middleware {name}';
    protected $description = 'Create a new GraphQL middleware class';
    protected $type = 'Middleware';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/middleware.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Middleware';
    }
}
