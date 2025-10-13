<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeDictionary extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:dictionary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new dictionary';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Dictionary';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'dictionary.php.stub';

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
    }
}
