<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Statamic\Console\Commands\Traits\RunsInPlease;

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
}

