<?php

namespace Statamic\Console\Commands;

use Exception;
use Illuminate\Support\Str;
use Facades\Statamic\Console\Processes\Composer;
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
        if (parent::handle() === false) {
            return false;
        }

        $projectPath = $this->getRelativePath($this->getPath($this->qualifyClass($this->getNameInput())));

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
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . str_plural($this->type);
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        $basePath = $this->laravel['path'];

        if ($addon = $this->argument('addon')) {
            $basePath = $this->getAddonPath($addon);
        }

        $path = $basePath.'/'.str_replace('\\', '/', $name).'.php';

        return $path;
    }

    /**
     * Get addon path.
     *
     * @param string $addon
     * @return string
     */
    protected function getAddonPath($addon)
    {
        try {
            return Composer::installedPath($addon);
        } catch (Exception $exception) {
            $fallbackPath = $this->laravel['path'];
        }

        if (! isset($this->shownAddonPathError)) {
            $this->error('Could not find path for specified addon, falling back to default path.');
            $this->shownAddonPathError = true;
        }

        return $fallbackPath;
    }

    /**
     * Get path relative to the project if possible, otherwise return absolute path.
     *
     * @param string $path
     * @return string
     */
    protected function getRelativePath($path)
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
