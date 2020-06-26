<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Events\Data\BlueprintFoundOnFile;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;

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

    public function find($handle): ?Blueprint
    {
        if (! $handle) {
            return null;
        }

        if ($cached = array_get($this->blueprints, $handle)) {
            event(new BlueprintFoundOnFile($cached));

            return $cached;
        }

        if (! File::exists($path = "{$this->directory}/{$handle}.yaml")) {
            if (! File::exists($path = "{$this->fallbackDirectory}/{$handle}.yaml")) {
                return null;
            }
        }

        $blueprint = (new Blueprint)
            ->setHandle($handle)
            ->setContents(YAML::parse(File::get($path)));

        event(new BlueprintFoundOnFile($blueprint));

        $this->blueprints[$handle] = $blueprint;

        return $blueprint;
    }

    public function all(): Collection
    {
        if (! File::exists($this->directory)) {
            return collect();
        }

        return File::withAbsolutePaths()
            ->getFilesByTypeRecursively($this->directory, 'yaml')
            ->map(function ($file) {
                $basename = str_after($file, str_finish($this->directory, '/'));
                $handle = str_before($basename, '.yaml');
                $handle = str_replace('/', '.', $handle);

                return (new Blueprint)
                    ->setHandle($handle)
                    ->setContents(YAML::file($file)->parse());
            })
            ->keyBy->handle();
    }

    public function save(Blueprint $blueprint)
    {
        if (! File::exists($this->directory)) {
            File::makeDirectory($this->directory);
        }

        File::put(
            "{$this->directory}/{$blueprint->handle()}.yaml",
            YAML::dump($blueprint->contents())
        );
    }

    public function delete(Blueprint $blueprint)
    {
        File::delete("{$this->directory}/{$blueprint->handle()}.yaml");
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
}
