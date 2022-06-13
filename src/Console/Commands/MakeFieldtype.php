<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
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

        if (! $this->option('php') && $this->argument('addon')) {
            $this->updateServiceProvider();
        }
    }

    /**
     * Generate Vue component.
     */
    protected function generateVueComponent()
    {
        $name = $this->getNameInput();
        $path = $this->getJsPath("components/fieldtypes/{$name}.vue");

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildVueComponent($name));

        $relativePath = $this->getRelativePath($path);

        if ($addon = $this->argument('addon')) {
            $this->wireUpAddonJs($addon);
        } else {
            $this->line("Your {$this->typeLower} Vue component awaits: <comment>{$relativePath}</comment>");
            $this->comment("Don't forget to import and register your Fieldtype component in resources/js/addon.js");
        }
    }

    protected function fieldtypeAlreadyExists()
    {
        return $this->files->exists($this->getAddonPath($this->argument('addon')).'/resources/js/addon.js');
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildVueComponent($name)
    {
        // TODO: Replace this with $this->makeFromStub()
        $component = $this->files->get($this->getStub('fieldtype.vue.stub'));

        $component = str_replace('DummyName', $name, $component);
        $component = str_replace('dummy_name', snake_case($name), $component);

        return $component;
    }

    /**
     * Wire up addon JS.
     *
     * @param  string  $addon
     */
    protected function wireUpAddonJs($addon)
    {
        $addonPath = $this->getAddonPath($addon);

        if ($this->fieldtypeAlreadyExists()) {
            $this->comment("Don't forget to import and register your new Fieldtype component in resources/js/addon.js");

            return;
        }

        $files = [
            'addon/webpack.mix.js.stub' => 'webpack.mix.js',
            'addon/package.json.stub' => 'package.json',
            'addon/addon.js.stub' => 'resources/js/addon.js',
        ];

        $data = [
            'name' => $this->getNameInput(),
            'package' => $this->package,
            'root_namespace' => $this->rootNamespace(),
        ];

        foreach ($files as $stub => $file) {
            $this->createFromStub($stub, $addonPath.'/'.$file, $data);
        }
    }

    /**
     * Update the Service Provider to register fieldtype components.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $scriptsValue = $factory->concat(
            new Dir,
            new String_('/../dist/js/addon.js')
        );

        $fieldtypeClassValue = $factory->classConstFetch('Fieldtypes\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('scripts', $scriptsValue)
                    ->add()->protected()->property('fieldtypes', $fieldtypeClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to register the Fieldtype class and scripts in your addon's service provider.");
        }
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
