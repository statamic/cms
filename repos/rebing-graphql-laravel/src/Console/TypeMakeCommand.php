<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:type')]
class TypeMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:type {name}';
    protected $description = 'Create a new GraphQL type class';
    protected $type = 'Type';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/type.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Types';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceGraphqlName($stub);
    }

    protected function replaceGraphqlName(string $stub): string
    {
        $graphqlName = $this->getNameInput();
        $graphqlName = \Safe\preg_replace('/Type$/', '', $graphqlName);

        return str_replace(
            'DummyGraphqlName',
            $graphqlName,
            $stub
        );
    }
}
