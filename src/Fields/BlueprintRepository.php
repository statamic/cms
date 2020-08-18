<?php

namespace Statamic\Fields;

use Closure;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class BlueprintRepository
{
    protected $directory;
    protected $fallbacks = [];

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
        if (! $blueprint) {
            return null;
        }

        $path = $this->directory.'/'.str_replace('.', '/', $blueprint).'.yaml';

        return File::exists($path)
            ? $this->makeBlueprintFromFile($path)
            : $this->findFallback($blueprint);
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
        $blueprint->writeFile();
    }

    public function delete(Blueprint $blueprint)
    {
        $blueprint->deleteFile();
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
        $namespace = str_replace('/', '.', $namespace);
        $namespaceDir = str_replace('.', '/', $namespace);
        $directory = $this->directory.'/'.$namespaceDir;

        if (! File::exists(Str::removeRight($directory, '/'))) {
            return collect();
        }

        return File::withAbsolutePaths()->getFilesByType($directory, 'yaml');
    }

    private function makeBlueprintFromFile($path)
    {
        [$namespace, $handle] = $this->getNamespaceAndHandle(
            Str::after(Str::before($path, '.yaml'), $this->directory.'/')
        );

        $contents = YAML::file($path)->parse();

        return (new Blueprint)
            ->setOrder(Arr::pull($contents, 'order'))
            ->setInitialPath($path)
            ->setHandle($handle)
            ->setNamespace($namespace ?? null)
            ->setContents($contents);
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
