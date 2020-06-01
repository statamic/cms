<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Input\InputOption;

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
    protected $stub = 'fieldtype.php.stub';

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

        if (! $this->option('php')) {
            $this->generateVueComponent();
        }
    }

    /**
     * Generate vue component.
     */
    protected function generateVueComponent()
    {
        $name = $this->getNameInput();
        $path = $this->getJsPath("components/fieldtypes/{$name}.vue");

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildVueComponent($name));

        $relativePath = $this->getRelativePath($path);

        if ($this->hiddenPathOutput) {
            return;
        }

        $this->comment("Your {$this->typeLower} vue component awaits at: {$relativePath}");
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildVueComponent($name)
    {
        $component = $this->files->get($this->getStub('fieldtype.vue.stub'));

        $component = str_replace('DummyName', $name, $component);
        $component = str_replace('dummy_name', snake_case($name), $component);

        return $component;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['php', '', InputOption::VALUE_NONE, 'Create only the PHP class for the field type and skip the VueJS component'],
        ]);
    }
}
