<?php

namespace Statamic\Console\Commands\Concerns;

use Archetype\Facades\PHPFile;
use Illuminate\Support\Facades\Process;
use Statamic\Support\Str;

use function Laravel\Prompts\confirm;

trait MakesVueComponents
{
    private function generateVueComponent(): void
    {
        $name = $this->getNameInput();
        $directory = Str::of($this->type)->lower()->plural();
        $path = $this->getJsPath("components/{$directory}/{$name}.vue");

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildVueComponent($name));

        $relativePath = $this->getRelativePath($path);

        if (! $this->cpJsExists()) {
            ($addon = $this->argument('addon'))
                ? $this->wireUpAddonJs($addon)
                : $this->wireUpAppJs();
        }

        $this->components->info("{$this->type} Vue component [{$relativePath}] created successfully.");

        $this->components->bulletList([
            "Don't forget to import and register your new {$this->type} Vue component in resources/js/cp.js",
            "For more information, see the documentation: <comment>{$this->vueComponentDocsUrl}</comment>",
        ]);

        $this->newLine();
    }

    private function buildVueComponent(string $name): string
    {
        $component = $this->files->get($this->getStub($this->vueComponentStub));

        $component = str_replace('DummyName', $name, $component);
        $component = str_replace('dummy_name', Str::snake($name), $component);

        return $component;
    }

    private function wireUpAppJs(): void
    {
        if (! $this->cpJsExists()) {
            if (confirm(
                label: "It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?",
                hint: 'You can always run this command later.'
            )) {
                $this->call('statamic:setup-cp-vite', ['--only-necessary' => true]);
            }
        }
    }

    private function wireUpAddonJs(string $addon): void
    {
        $addonPath = $this->getAddonPath($addon);

        $files = [
            'addon/vite.config.js.stub' => 'vite.config.js',
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

        $this->files->makeDirectory($addonPath.'/resources/dist', 0777, true, true);

        Process::path(base_path())->run('npm install', function (string $type, string $buffer) {
            echo $buffer;
        });

        $this->configureViteInAddonServiceProvider();

        $this->call('vendor:publish', ['--tag' => 'statamic-cp-dev']);
    }

    private function cpJsExists(): bool
    {
        if ($addon = $this->argument('addon')) {
            return $this->files->exists($this->getAddonPath($addon).'/resources/js/cp.js')
                || $this->files->exists($this->getAddonPath($addon).'/resources/js/addon.js');
        }

        return $this->files->exists(base_path('resources/js/cp.js'));
    }

    private function configureViteInAddonServiceProvider(): void
    {
        try {
            PHPFile::load("addons/{$this->package}/src/ServiceProvider.php")
                ->add()->protected()->property('vite', [
                    'input' => ['resources/js/addon.js'],
                    'publicDirectory' => 'resources/dist',
                ])
                ->save();
        } catch (\Exception $e) {
            $this->comment("Don't forget to configure Vite in your addon's service provider.");
        }
    }
}
