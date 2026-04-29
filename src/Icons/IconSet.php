<?php

namespace Statamic\Icons;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\Path;
use Statamic\Support\Str;

class IconSet
{
    private string $directory;
    private Filesystem $filesystem;

    public function __construct(private string $name, string $directory)
    {
        $this->directory = Str::removeRight(Path::tidy($directory), '/');
        $this->filesystem = app(Filesystem::class);
    }

    public function name()
    {
        return $this->name;
    }

    public function directory()
    {
        return $this->directory;
    }

    public function names()
    {
        return $this->files()->map->getBasename('.svg')->values();
    }

    public function contents()
    {
        return $this->files()->map->getContents()->all();
    }

    public function get(string $name): string
    {
        return $this->filesystem->get($this->directory.'/'.$name.'.svg');
    }

    private function files(): Collection
    {
        return collect($this->filesystem->files($this->directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'svg')
            ->keyBy(fn ($file) => $file->getBasename('.svg'));
    }
}
