<?php

namespace Statamic\Fields;

use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class BlueprintRepository
{
    protected $blueprints = [];
    protected $files;
    protected $directory;
    protected $fallbackDirectory;

    public function setDirectory(string $directory)
    {
        $this->directory = Path::tidy($directory);

        return $this;
    }

    public function setFallbackDirectory(string $directory)
    {
        $this->fallbackDirectory = $directory;

        return $this;
    }

    public function find($blueprint): ?Blueprint
    {
        if (! $blueprint) {
            return null;
        }

        $path = str_replace('.', '/', $blueprint);

        if ($cached = array_get($this->blueprints, $path)) {
            return $cached;
        }

        if (! File::exists($file = "{$this->directory}/{$path}.yaml")) {
            if (! File::exists($file = "{$this->fallbackDirectory}/{$path}.yaml")) {
                return null;
            }
        }

        $str = str_replace('/', '.', $path);
        $parts = explode('.', $str);
        $handle = array_pop($parts);
        $namespace = implode('.', $parts);

        $blueprint = (new Blueprint)
            ->setHandle($handle)
            ->setNamespace(empty($namespace) ? null : $namespace)
            ->setContents(YAML::file($file)->parse());

        $this->blueprints[$handle] = $blueprint;

        return $blueprint;
    }

    public function save(Blueprint $blueprint)
    {
        if (! File::exists($this->directory)) {
            File::makeDirectory($this->directory);
        }

        File::put($this->path($blueprint), YAML::dump($blueprint->contents()));
    }

    public function delete(Blueprint $blueprint)
    {
        File::delete($this->path($blueprint));
    }

    private function path($blueprint)
    {
        return Path::tidy(vsprintf('%s/%s/%s.yaml', [
            $this->directory,
            str_replace('.', '/', $blueprint->namespace()),
            $blueprint->handle(),
        ]));
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
        });

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
        $namespace = str_replace('/', '.', $namespace);
        $dir = str_replace('.', '/', $namespace);
        $path = $this->directory.'/'.$dir;

        if (! File::exists($path)) {
            return collect();
        }

        return File::withAbsolutePaths()
            ->getFilesByType($path, 'yaml')
            ->map(function ($file) use ($dir, $namespace) {
                $basename = Str::after($file, Str::finish($dir, '/'));
                $handle = Str::before($basename, '.yaml');

                return (new Blueprint)
                    ->setHandle($handle)
                    ->setNamespace($namespace)
                    ->setContents(YAML::file($file)->parse());
            })
            ->keyBy->handle();
    }
}
