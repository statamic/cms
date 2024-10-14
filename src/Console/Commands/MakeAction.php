<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeAction extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'action.php.stub';
}
