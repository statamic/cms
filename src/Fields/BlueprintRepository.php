<?php

namespace Statamic\Fields;

use Closure;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class BlueprintRepository
{
    private const BLINK_FOUND = 'blueprints.found';
    private const BLINK_FROM_FILE = 'blueprints.from-file';
    private const BLINK_NAMESPACE_PATHS = 'blueprints.paths-in-namespace';

    protected $directories = [];
    protected $fallbacks = [];

    public function setDirectory(array|string $directories)
    {
        if (is_string($directories)) {
            $directories = [$directories];
        }

        $this->directories = array_map(fn ($directory) => Path::tidy($directory), $directories);

        return $this;
    }

    public function directory()
    {
        return array_first($this->directories);
    }

    public function directories()
    {
        return $this->directories;
    }

    public function path(string $blueprint): ?string
    {
        $path = str_replace('.', '/', $blueprint);

        foreach ($this->directories as $directory) {
            if (File::exists("{$directory}/{$path}.yaml")) {
                return Path::tidy(vsprintf('%s/%s.yaml', [
                    $directory,
                    $path,
                ]));
            }
        }

        return null;
    }

    public function fallbackPath($handle)
    {
        [$namespace, $handle] = $this->getNamespaceAndHandle($handle);

        return Path::tidy(vsprintf('%s/%s/%s.yaml', [
            $this->directory(),
            str_replace('.', '/', $namespace),
            $handle,
        ]));
    }

    private function blueprintDirectory(string $blueprint): ?string
    {
        $path = str_replace('.', '/', $blueprint);

        foreach ($this->directories as $directory) {
            if (Str::startsWith($blueprint, $directory)) {
                return $directory;
            }
        }

        return null;
    }

    public function find($blueprint): ?Blueprint
    {
        return Blink::store(self::BLINK_FOUND)->once($blueprint, function () use ($blueprint) {
            if (! $blueprint) {
                return null;
            }

            return ($path = $this->path($blueprint)) !== null
                ? $this->makeBlueprintFromFile($path)
                : $this->findFallback($blueprint);
        });
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
        $this->clearBlinkCaches();

        $blueprint->deleteFile();
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
            $blueprint->setHandle($handle);
        }

        return $blueprint;
    }

    public function makeFromFields($fields)
    {
        $fields = collect($fields)->map(function ($field, $handle) {
            return compact('handle', 'field');
        })->values()->all();

        return (new Blueprint)->setContents(['fields' => $fields]);
    }

    public function makeFromSections($sections)
    {
        $sections = collect($sections)->map(function ($section, $section_handle) {
            $fields = collect($section['fields'])->map(function ($field, $handle) {
                return compact('handle', 'field');
            });

            $section['fields'] = $fields;

            return $section;
        })->all();

        return (new Blueprint)->setContents(compact('sections'));
    }

    public function in(string $namespace)
    {
        return $this
            ->filesIn($namespace)
            ->map(function ($file) {
                return $this->makeBlueprintFromFile($file);
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

    private function filesIn($namespace)
    {
        return Blink::store(self::BLINK_NAMESPACE_PATHS)->once($namespace, function () use ($namespace) {
            $files = collect();

            foreach ($this->directories as $directory) {
                $namespace = str_replace('/', '.', $namespace);
                $namespaceDir = str_replace('.', '/', $namespace);
                $directory = $directory.'/'.$namespaceDir;

                if (! File::exists(Str::removeRight($directory, '/'))) {
                    continue;
                }

                File::withAbsolutePaths()
                    ->getFilesByType($directory, 'yaml')
                    ->each(function ($blueprint) use ($files) {
                        $files->push($blueprint);
                    });
            }

            return $files;
        });
    }

    private function makeBlueprintFromFile($path)
    {
        return Blink::store(self::BLINK_FROM_FILE)->once($path, function () use ($path) {
            [$namespace, $handle] = $this->getNamespaceAndHandle(
                Str::after(
                    Str::before($path, '.yaml'),
                    $this->blueprintDirectory($path).'/'
                )
            );

            $contents = YAML::file($path)->parse();

            return (new Blueprint)
                ->setHidden(Arr::pull($contents, 'hide'))
                ->setOrder(Arr::pull($contents, 'order'))
                ->setInitialPath($path)
                ->setHandle($handle)
                ->setNamespace($namespace ?? null)
                ->setContents($contents);
        });
    }

    private function getNamespaceAndHandle($blueprint)
    {
        $blueprint = str_replace('/', '.', $blueprint);
        $parts = explode('.', $blueprint);
        $handle = array_pop($parts);
        $namespace = implode('.', $parts);
        $namespace = empty($namespace) ? null : $namespace;

        return [$namespace, $handle];
    }
}
