<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeFilter extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new filter';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Scope';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'filter.php.stub';
}
