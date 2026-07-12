<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

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
    protected $description = 'Create a new modifier';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Modifier';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'modifier.php.stub';
}
