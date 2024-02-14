<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:query')]
class QueryMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:query {name}';
    protected $description = 'Create a new GraphQL query class';
    protected $type = 'Query';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/query.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Queries';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceGraphqlName($stub);
    }

    protected function replaceGraphqlName(string $stub): string
    {
        $graphqlName = lcfirst($this->getNameInput());
        $graphqlName = \Safe\preg_replace('/Query$/', '', $graphqlName);

        return str_replace(
            'DummyGraphqlName',
            $graphqlName,
            $stub
        );
    }
}
