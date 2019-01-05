<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Input\InputArgument;

class MakeModifier extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:modifier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new modifier addon';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Modifier';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        // TODO: Handle optional `addon` location argument.

        return parent::handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/modifier.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Modifiers';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['addon', InputArgument::OPTIONAL, 'The name of your addon'],
        ]);
    }
}

