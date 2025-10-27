<?php

namespace Statamic\Console\Commands;

use Statamic\Console\Commands\Concerns\MakesVueComponents;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Input\InputOption;

class MakeFieldtype extends GeneratorCommand
{
    use MakesVueComponents, RunsInPlease;

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
    protected $description = 'Create a new fieldtype';

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
     * The stub to be used for generating the Vue component.
     *
     * @var string
     */
    protected $vueComponentStub = 'fieldtype.vue.stub';

    /**
     * The URL to the documentation for Vue components.
     *
     * @var string
     */
    protected $vueComponentDocsUrl = 'https://statamic.dev/fieldtypes#vue-components';

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
            $this->configureViteInAddonServiceProvider();
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
