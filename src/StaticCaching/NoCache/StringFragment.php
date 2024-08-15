<?php

namespace Statamic\StaticCaching\NoCache;

use Statamic\Facades\File;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class StringFragment
{
    private $region;
    private $contents;
    private $extension;
    private $data;
    private $directory;

    public function __construct($region, $contents, $extension, $data)
    {
        $this->region = $region;
        $this->contents = $contents;
        $this->extension = $extension;
        $this->data = $data;
        $this->directory = config('view.compiled').'/nocache';
    }

    public function render(): string
    {
        view()->addNamespace('nocache', $this->directory);
        File::makeDirectory($this->directory);

        $path = $this->createTemporaryView($view = $this->region.Str::random());

        $this->data['__frontmatter'] = Arr::pull($this->data, 'view', []);

        $rendered = view('nocache::'.$view, $this->data)->render();

        File::delete($path);

        return $rendered;
    }

    private function createTemporaryView($view): string
    {
        $path = vsprintf('%s/%s.%s', [
            $this->directory,
            $view,
            $this->extension,
        ]);

        File::put($path, $this->contents);

        return $path;
    }
}
