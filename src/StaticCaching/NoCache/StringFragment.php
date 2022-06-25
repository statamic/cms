<?php

namespace Statamic\StaticCaching\NoCache;

use Statamic\Facades\File;
use Statamic\Support\Arr;

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

        $this->createTemporaryView();

        $this->data['__frontmatter'] = Arr::pull($this->data, 'view', []);

        return view('nocache::'.$this->region, $this->data)->render();
    }

    private function createTemporaryView()
    {
        $path = vsprintf('%s/%s.%s', [
            $this->directory,
            $this->region,
            $this->extension,
        ]);

        if (File::exists($path)) {
            return;
        }

        File::put($path, $this->contents);
    }
}
