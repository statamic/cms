<?php

namespace Statamic\CP\Icons;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Path;
use Statamic\Support\Str;

class IconSet
{
    public function __construct(private string $name, private string $directory)
    {
        //
    }

    public function name()
    {
        return $this->name;
    }

    public function directory()
    {
        return $this->directory;
    }

    public function contents()
    {
        $path = Str::removeRight(Path::tidy($this->directory), '/');

        return collect(app(Filesystem::class)->files($path))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'svg')
            ->keyBy(fn ($file) => pathinfo($file->getBasename(), PATHINFO_FILENAME))
            ->map
            ->getContents()
            ->all();
    }
}
