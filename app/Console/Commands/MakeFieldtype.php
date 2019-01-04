<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeFieldtype extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:fieldtype';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new fieldtype addon';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Fieldtype';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        // TODO: Handle optional `addon` location argument.

        $name = $this->getNameInput();

        if ((! $this->hasOption('force') || ! $this->option('force')) && $this->alreadyExists($name)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->generateClass($name);
        $this->generateVueComponent($name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/fieldtype.stub';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getVueStub()
    {
        return __DIR__.'/stubs/fieldtype.vue.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return studly_case(parent::getNameInput());
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Fieldtypes';
    }

    /**
     * Generate class.
     *
     * @param string $name
     */
    protected function generateClass($name)
    {
        $class = $this->qualifyClass($name);
        $path = $this->getPath($class);

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($class));

        $this->info($this->type.' php class created successfully.');
    }

    /**
     * Generate vue component.
     *
     * @param string $name
     */
    protected function generateVueComponent($name)
    {
        $path = version_compare(app()::VERSION, '5.7.0', '<')
            ? resource_path("assets/js/components/{$name}.vue")
            : resource_path("js/components/{$name}.vue");

        $this->files->put($path, $this->buildVueComponent($name));

        $this->info($this->type.' vue component created successfully.');
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildVueComponent($name)
    {
        $component = $this->files->get($this->getVueStub());

        $component = str_replace('DummyName', $name, $component);
        $component = str_replace('dummy_name', snake_case($name), $component);

        return $component;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['addon', InputArgument::OPTIONAL, 'The name of your addon'],
        ]);
    }
}
