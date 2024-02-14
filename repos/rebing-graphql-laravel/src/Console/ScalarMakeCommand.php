<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:scalar')]
class ScalarMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:scalar {name}';
    protected $description = 'Create a new GraphQL scalar class';
    protected $type = 'Scalar';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/scalar.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Scalars';
    }
}
