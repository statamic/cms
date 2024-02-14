<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:field')]
class FieldMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:field {name}';
    protected $description = 'Create a new GraphQL field class';
    protected $type = 'Field';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/field.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Fields';
    }
}
