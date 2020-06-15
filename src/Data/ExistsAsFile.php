<?php

namespace Statamic\Data;

use Illuminate\Support\Carbon;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;

trait ExistsAsFile
{
    protected $initialPath;

    abstract public function path();

    public function initialPath($path = null)
    {
        if (func_num_args() === 0) {
            return $this->initialPath;
        }

        $this->initialPath = $path;

        return $this;
    }

    public function fileData()
    {
        return array_merge($this->data(), [
            'id' => $this->id(),
        ]);
    }

    public function fileContents()
    {
        // This method should be clever about what contents to output depending on the
        // file type used. Right now it's assuming markdown. Maybe you'll want to
        // save JSON, etc. TODO: Make it smarter when the time is right.

        $data = Arr::removeNullValues($this->fileData());

        if ($this->fileExtension() === 'yaml') {
            return YAML::dump($data);
        }

        $content = array_pull($data, 'content');

        return YAML::dumpFrontMatter($data, $content);
    }

    public function fileLastModified()
    {
        if (! File::exists($this->path())) {
            return Carbon::now();
        }

        return Carbon::createFromTimestamp(File::lastModified($this->path()));
    }

    public function fileExtension()
    {
        return 'yaml';
    }

    public function writeFile()
    {
        $path = $this->path();
        $initial = $this->initialPath();

        if ($initial && $path !== $initial) {
            File::delete($this->initialPath());
        }

        File::put($path, $this->fileContents());

        $this->initialPath($path);
    }

    public function deleteFile()
    {
        File::delete($this->path());
    }
}
