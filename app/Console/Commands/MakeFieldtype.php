<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;

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
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'fieldtype.stub';

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

        $this->generateVueComponent();
    }

    /**
     * Generate vue component.
     */
    protected function generateVueComponent()
    {
        $name = $this->getNameInput();

        // TODO: Maybe instead of checking version, we just check if `assets/js` exists 🤔
        // It's possible they started with a 5.6 app and shifted to 5.7+, but kept old structure
        $path = version_compare(app()::VERSION, '5.7.0', '<')
            ? resource_path("assets/js/components/{$name}.vue")
            : resource_path("js/components/{$name}.vue");

        $this->files->put($path, $this->buildVueComponent($name));

        $projectPath = $this->getProjectPath($path);

        $this->comment("Your {$this->type} vue component awaits at: {$projectPath}");
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
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getVueStub()
    {
        return __DIR__.'/stubs/fieldtype.vue.stub';
    }
}
