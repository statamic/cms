<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Statamic\Support\Str;

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
    protected $description = 'Create a new tag';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Tag';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'tag.php.stub';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $class = str_replace('dummy_tag', Str::snake($this->getNameInput()), $class);

        return $class;
    }
}
