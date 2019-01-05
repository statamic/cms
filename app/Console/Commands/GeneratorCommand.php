<?php

namespace Statamic\Console\Commands;

use Illuminate\Support\Str;
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

        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        $projectPath = str_replace(base_path().'/', '', $path);

        $this->comment("Your {$this->type} class awaits at: {$projectPath}");
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
}
