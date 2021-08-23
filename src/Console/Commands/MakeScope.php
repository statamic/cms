<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
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
    protected $description = 'Create a new query scope addon';

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
     * Update the Service Provider to register the scope component.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $scopeClassValue = $factory->classConstFetch('Scopes\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('scopes', $scopeClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Scope class in your addon's service provider.");
        }
    }
}
