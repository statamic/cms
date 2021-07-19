<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
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
    protected $description = 'Create a new filter addon';

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

        if ($this->argument('addon')) {
            $this->updateServiceProvider();
        }
    }

    /**
     * Update the Service Provider to register the Filter component.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $filterClassValue = $factory->classConstFetch('Filters\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('filters', $filterClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Filter class in your addon's service provider.");
        }
    }
}
