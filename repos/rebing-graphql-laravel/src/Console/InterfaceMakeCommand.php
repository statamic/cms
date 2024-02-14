<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('make:graphql:interface')]
class InterfaceMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:graphql:interface {name}';
    protected $description = 'Create a new GraphQL interface class';
    protected $type = 'Interface';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/interface.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\GraphQL\Interfaces';
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
