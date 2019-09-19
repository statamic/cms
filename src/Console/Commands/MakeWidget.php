<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeWidget extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new widget addon';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'widget.php.stub';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $class = str_replace('widget_view', str_slug(snake_case($this->getNameInput())), $class);

        return $class;
    }
}
