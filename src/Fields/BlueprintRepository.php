<?php

namespace Statamic\Fields;

use Closure;
use Statamic\Exceptions\BlueprintNotFoundException;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Hooks\CP\Blueprint as BlueprintHook;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class BlueprintRepository
{
    protected const BLINK_FOUND = 'blueprints.found';
    protected const BLINK_FROM_FILE = 'blueprints.from-file';
    protected const BLINK_NAMESPACE_PATHS = 'blueprints.paths-in-namespace';

    protected $directory;
    protected $fallbacks = [];
    protected $additionalNamespaces = [];

    public function setDirectory(string $directory)
    {
        $this->directory = Path::tidy($directory);

        return $this;
    }

    public function directory()
    {
        return $this->directory;
    }

    public function find($blueprint): ?Blueprint
    {
        return Blink::store(self::BLINK_FOUND)->once($blueprint, function () use ($blueprint) {
            if (! $blueprint) {
                return null;
            }

            $parts = explode('::', $blueprint);

            $path = count($parts) > 1
                ? $this->findNamespacedBlueprintPath($blueprint)
                : $this->findStandardBlueprintPath($blueprint);

            return $path !== null && File::exists($path)
                ? $this->makeBlueprintFromFile($path, count($parts) > 1 ? $parts[0] : null)
                : $this->findFallback($blueprint);
        });
    }

    public function findOrFail($id): Blueprint
    {
        $blueprint = $this->find($id);

        if (! $blueprint) {
            throw new BlueprintNotFoundException($id);
        }

        return $blueprint;
    }

    public function findStandardBlueprintPath($handle)
    {
        if (Str::startsWith($handle, 'vendor.')) {
            return null;
        }

        return $this->directory.'/'.str_replace('.', '/', $handle).'.yaml';
    }

    public function findNamespacedBlueprintPath($handle)
    {
        [$namespace, $handle] = explode('::', $handle);
        $namespaceDir = str_replace('.', '/', $namespace);
        $handle = str_replace('/', '.', $handle);
        $path = str_replace('.', '/', $handle);

        $overridePath = "{$this->directory}/vendor/{$namespaceDir}/{$path}.yaml";

        if (File::exists($overridePath)) {
            return $overridePath;
        }

        if (! isset($this->additionalNamespaces[$namespace])) {
            return null;
        }

        return "{$this->additionalNamespaces[$namespace]}/{$path}.yaml";
    }

    public function setFallback($handle, Closure $blueprint)
    {
        $handle = str_replace('/', '.', $handle);

        $this->fallbacks[$handle] = $blueprint;

        return $this;
    }

    public function findFallback($handle)
    {
        if (! $blueprint = $this->fallbacks[$handle] ?? null) {
            return null;
        }

        [$namespace, $handle] = $this->getNamespaceAndHandle($handle);

        return $blueprint()->setHandle($handle)->setNamespace($namespace);
    }

    public function save(Blueprint $blueprint)
    {
        $this->clearBlinkCaches();

        $blueprint->writeFile();
    }

    public function delete(Blueprint $blueprint)
    {
        if ($blueprint->isNamespaced()) {
            throw new \Exception('Namespaced blueprints cannot be deleted');
        }

        $this->clearBlinkCaches();

        $blueprint->deleteFile();
    }

    public function reset(Blueprint $blueprint)
    {
        if (! $blueprint->isNamespaced()) {
            throw new \Exception('Non-namespaced blueprints cannot be reset');
        }

        File::delete($blueprint->path());
    }

    private function clearBlinkCaches()
    {
        Blink::store(self::BLINK_FOUND)->flush();
        Blink::store(self::BLINK_FROM_FILE)->flush();
        Blink::store(self::BLINK_NAMESPACE_PATHS)->flush();
    }

    public function make($handle = null)
    {
        $blueprint = new Blueprint;

        if ($handle) {
            $handle = explode('::', $handle);

            if (count($handle) > 1) {
                $namespace = array_shift($handle);
                $blueprint->setNamespace($namespace);
            }

            $handle = implode('::', $handle);

            $blueprint->setHandle($handle);
        }

        return $blueprint;
    }

    public function makeFromFields($fields)
    {
        $fields = collect($fields)->map(function ($field, $handle) {
            return compact('handle', 'field');
        })->values()->all();

        return $this->make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => $fields,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function makeFromTabs($tabs)
    {
        $tabs = collect($tabs)->map(function ($tab, $tab_handle) {
            $fields = collect($tab['fields'])->map(function ($field, $handle) {
                return compact('handle', 'field');
            });

            $tab['fields'] = $fields;

            return $tab;
        })->all();

        return $this->make()->setContents(compact('tabs'));
    }

    public function in(string $namespace)
    {
        return $this
            ->filesIn($namespace)
            ->map(function ($file) use ($namespace) {
                return $this->makeBlueprintFromFile($file, $namespace);
            })
            ->sort(function ($a, $b) {
                $orderA = $a->order() ?? 99999;
                $orderB = $b->order() ?? 99999;

                return $orderA === $orderB
                    ? $a->title() <=> $b->title()
                    : $orderA <=> $orderB;
            })
            ->keyBy->handle();
    }

    public function addNamespace(string $namespace, string $directory): void
    {
        $this->additionalNamespaces[$namespace] = $directory;
    }

    public function getAdditionalNamespaces()
    {
        return collect($this->additionalNamespaces);
    }

    protected function filesIn($namespace)
    {
        return Blink::store(self::BLINK_NAMESPACE_PATHS)->once($namespace, function () use ($namespace) {
            $namespace = str_replace('/', '.', $namespace);
            $namespaceDir = str_replace('.', '/', $namespace);
            $directory = $this->directory.'/'.$namespaceDir;

            if (isset($this->additionalNamespaces[$namespace])) {
                $directory = "{$this->additionalNamespaces[$namespace]}";
            }

            $files = File::withAbsolutePaths()
                ->getFilesByType($directory, 'yaml')
                ->mapWithKeys(fn ($path) => [Str::after($path, $directory.'/') => $path]);

            if (File::exists($directory = $this->directory.'/vendor/'.$namespaceDir)) {
                $overrides = File::withAbsolutePaths()
                    ->getFilesByType($directory, 'yaml')
                    ->mapWithKeys(fn ($path) => [Str::after($path, $directory.'/') => $path]);

                $files = $files->merge($overrides)->values();
            }

            return $files;
        });
    }

    protected function makeBlueprintFromFile($path, $namespace = null)
    {
        $hook = (new BlueprintHook())->makeFromFile($path, $namespace);
        [$path, $namespace] = [$hook->path, $hook->namespace];

        return Blink::store(self::BLINK_FROM_FILE)->once($path, function () use ($path, $namespace) {
            if (! $namespace || ! isset($this->additionalNamespaces[$namespace])) {
                [$namespace, $handle] = $this->getNamespaceAndHandle(
                    Str::after(Str::before($path, '.yaml'), $this->directory.'/')
                );
            } else {
                $handle = Str::of($path)->afterLast('/')->before('.');
            }

            $contents = YAML::file($path)->parse();

            return $this->make($handle)
                ->setHidden(Arr::pull($contents, 'hide'))
                ->setOrder(Arr::pull($contents, 'order'))
                ->setInitialPath($path)
                ->setNamespace($namespace ?? null)
                ->setContents($contents);
        });
    }

    protected function getNamespaceAndHandle($blueprint)
    {
        $blueprint = str_replace('/', '.', $blueprint);
        $parts = explode('.', $blueprint);
        $handle = array_pop($parts);
        $namespace = implode('.', $parts);
        $namespace = empty($namespace) ? null : $namespace;

        return [$namespace, $handle];
    }
}
