<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Input\InputArgument;

class MakeAddon extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:addon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new addon';

    /**
     * The name of the addon.
     *
     * @var string
     */
    protected $addonName;

    /**
     * The path to where the addon will be generated.
     *
     * @var string
     */
    protected $addonPath;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->addonName = $this->getNameInput();
        $this->addonPath = config('statamic.system.addons_path') . "/{$this->addonName}";

        $this->generateComposerJson();
        $this->generateServiceProvider();

        // TODO: add path in app's composer.json
        // TODO: run composer require
        // TODO: handle flags for additional scaffolding

        $relativePath = $this->getRelativePath($this->addonPath);

        $this->info('Addon created successfully.');
        $this->comment("Your addon files await at: {$relativePath}");
    }

    /**
     * Generate composer.json.
     */
    protected function generateComposerJson()
    {
        $json = $this->files->get($this->getStub('addon/composer.json.stub'));

        $json = str_replace('DummyNamespace', str_replace('\\', '\\\\', $this->addonNamespace()), $json);
        $json = str_replace('dummy-slug', $slug = str_slug(snake_case($this->addonName)), $json);
        $json = str_replace('DummyTitle', str_replace('-', ' ', title_case($slug)), $json);

        $this->files->put($this->addonPath('composer.json'), $json);
    }

    /**
     * Build absolute path for an addon file.
     *
     * @param string $path
     * @return string
     */
    protected function addonPath($path)
    {
        $path = "{$this->addonPath}/{$path}";

        $this->makeDirectory($path);

        return $path;
    }

    /**
     * Build addon namespace.
     *
     * @return string
     */
    protected function addonNamespace()
    {
        return "Local\\{$this->addonName}";
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the addon'],
        ];
    }
}
