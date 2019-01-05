<?php

namespace Statamic\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\GeneratorCommand as IlluminateGeneratorCommand;

abstract class GeneratorCommand extends IlluminateGeneratorCommand
{
    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        // TODO: Handle optional `addon` location argument.

        if (parent::handle() === false) {
            return false;
        }

        $projectPath = $this->getProjectPath($this->getPath($this->qualifyClass($this->getNameInput())));

        $this->comment("Your {$this->type} class awaits at: {$projectPath}");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/' . $this->stub;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return studly_case(parent::getNameInput());
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . str_plural($this->type);
    }

    /**
     * Get path relative to the project if possible, otherwise return absolute path.
     *
     * @param string $path
     * @return string
     */
    protected function getProjectPath($path)
    {
        return str_replace(base_path().'/', '', $path);
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
