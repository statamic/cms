<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Statamic\Support\Str;

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

        $filename = Str::slug(Str::snake($this->getNameInput()));

        $this->createFromStub(
            'widget.blade.php.stub',
            $path."/resources/views/widgets/{$filename}.blade.php",
            $data
        );
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

        $name = Str::slug(Str::snake($this->getNameInput()));
        $viewPath = 'widgets.'.$name;

        if ($this->argument('addon')) {
            $viewPath = $name.'::'.$viewPath;
        }

        $class = str_replace('widget_view', $viewPath, $class);

        return $class;
    }
}
