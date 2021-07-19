<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
use Statamic\Console\RunsInPlease;

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
    protected $description = 'Create a new tag addon';

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
     * Update the Service Provider to register the Tag component.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $tagsClassValue = $factory->classConstFetch('Tags\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('tags', $tagsClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Tag class in your addon's service provider.");
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $class = str_replace('dummy_tag', snake_case($this->getNameInput()), $class);

        return $class;
    }
}
