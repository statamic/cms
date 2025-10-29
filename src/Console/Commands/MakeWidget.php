<?php

namespace Statamic\Console\Commands;

use Statamic\Console\Commands\Concerns\MakesVueComponents;
use Statamic\Console\RunsInPlease;
use Statamic\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeWidget extends GeneratorCommand
{
    use MakesVueComponents, RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new widget';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'widget.php.stub';

    /**
     * The stub to be used for generating the Vue component.
     *
     * @var string
     */
    protected $vueComponentStub = 'widget.vue.stub';

    /**
     * The URL to the documentation for Vue components.
     *
     * @var string
     */
    protected $vueComponentDocsUrl = 'https://statamic.dev/widgets#vue-components';

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

        $this->option('blade')
            ? $this->generateWidgetView()
            : $this->generateVueComponent();
    }

    /**
     * Generate the widget view file.
     *
     * @param  string  $addon
     */
    protected function generateWidgetView()
    {
        $addon = $this->argument('addon');
        $basePath = $addon ? $this->getAddonPath($addon) : base_path();

        $data = [
            'name' => $this->getNameInput(),
        ];

        $filename = Str::slug(Str::snake($this->getNameInput()));
        $path = "resources/views/widgets/{$filename}.blade.php";

        $this->createFromStub('widget.blade.php.stub', $basePath.'/'.$path, $data);

        $this->components->info(sprintf('View [%s] created successfully.', $path));
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        if ($this->option('blade')) {
            $stub = $this->files->get($this->getStub('blade_widget.php.stub'));
            $class = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

            $name = Str::slug(Str::snake($this->getNameInput()));
            $viewPath = 'widgets.'.$name;

            if ($this->argument('addon')) {
                $viewPath = $name.'::'.$viewPath;
            }

            return str_replace('widget_view', $viewPath, $class);
        }

        $class = parent::buildClass($name);

        $class = str_replace('DummyComponent', $this->getNameInput(), $class);

        return $class;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['blade', '', InputOption::VALUE_NONE, 'Create a Blade view for the widget instead of a Vue component'],
        ]);
    }
}
