<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
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
    protected $description = 'Create a new dictionary addon';

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

        if ($this->argument('addon')) {
            $this->updateServiceProvider();
        }
    }

    /**
     * Update the Service Provider to register dictionary components.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $dictionaryClassValue = $factory->classConstFetch('Dictionaries\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                ->add()->protected()->property('dictionaries', $dictionaryClassValue)
                ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Dictionary class in your addon's service provider.");
        }
    }
}
