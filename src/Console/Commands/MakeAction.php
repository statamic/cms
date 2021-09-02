<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
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
     * Update the Service Provider to register the Action component.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $actionClassValue = $factory->classConstFetch('Actions\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('actions', $actionClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Action class in your addon's service provider.");
        }
    }
}
