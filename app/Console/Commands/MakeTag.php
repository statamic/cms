<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeTag extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:tag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tag addon';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Tag';

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
        return __DIR__.'/stubs/tag.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Tags';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $class = str_replace('dummy_addon', snake_case($this->getNameInput()), $class);

        return $class;
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

