<?php

namespace Statamic\Console\Commands;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderFactory;
use Statamic\Console\RunsInPlease;

class MakeWidget extends GeneratorCommand
{
    use RunsInPlease;

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
    protected $description = 'Create a new widget addon';

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
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }

        $this->generateWidgetView();

        if ($this->argument('addon')) {
            $this->updateServiceProvider();
        }
    }

    /**
     * Generate the widget view file.
     *
     * @param  string  $addon
     */
    protected function generateWidgetView()
    {
        $addon = $this->argument('addon');
        $path = $addon ? $this->getAddonPath($addon) : base_path();

        $data = [
            'name' => $this->getNameInput(),
        ];

        $filename = str_slug(snake_case($this->getNameInput()));

        $this->createFromStub(
            'widget.blade.php.stub',
            $path."/resources/views/widgets/{$filename}.blade.php",
            $data
        );
    }

    /**
     * Update the Service Provider to register the widget component.
     */
    protected function updateServiceProvider()
    {
        $factory = new BuilderFactory();

        $widgetClassValue = $factory->classConstFetch('Widgets\\'.$this->getNameInput(), 'class');

        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                    ->add()->protected()->property('widgets', $widgetClassValue)
                    ->save();
        } catch (\Exception $e) {
            $this->info("Don't forget to register the Widget class in your addon's service provider.");
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

        $name = str_slug(snake_case($this->getNameInput()));
        $viewPath = 'widgets.'.$name;

        if ($this->argument('addon')) {
            $viewPath = $name.'::'.$viewPath;
        }

        $class = str_replace('widget_view', $viewPath, $class);

        return $class;
    }
}
