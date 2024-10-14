<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeScope extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:scope';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new query scope';

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
    protected $stub = 'scope.php.stub';
}
