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

        if ($addon = $this->argument('addon')) {
            $this->wireUpAddonJs($addon);
        } else {
            // $this->wireUpAppJs(); // TODO!
        }

        if ($this->hiddenPathOutput) {
            return;
        }

        $this->comment("Your {$this->typeLower} Vue component awaits at: {$relativePath}");
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
     * Wire up addon JS.
     *
     * @param string $addon
     */
    protected function wireUpAddonJs($addon)
    {
        $addonPath = $this->getAddonPath($addon);

        $files = [
            'addon/webpack.mix.js.stub' => '/../webpack.mix.js',
            'addon/package.json.stub' => '/../package.json',
            'addon/addon.js.stub' => '/resources/js/addon.js',
            'addon/.gitignore.stub' => '/../.gitignore',
            'addon/README.md.stub' => '/../README.md',
        ];

        foreach ($files as $stub => $file) {
            if (! $this->files->exists($path = $addonPath.$file)) {
                $this->files->put($path, $this->files->get($this->getStub($stub)));
            }
        }

        // TODO: Append a line to register fieldtype to addon.js
        // TODO: Parse stubs with Antlers to inject proper names
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
