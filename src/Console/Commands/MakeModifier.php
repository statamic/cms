<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
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
    protected $description = 'Create a new modifier addon';

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
     * Update the Service Provider to register fieldtype components.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $modifierClassValue = $factory->classConstFetch('Modifiers\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('modifiers', $modifierClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Modifier class in your addon's service provider.");
        }
    }
}
