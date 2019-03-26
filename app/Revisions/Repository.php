<?php

namespace Statamic\Revisions;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Folder;
use Illuminate\Support\Carbon;

class Repository
{
    public function directory()
    {
        return config('statamic.revisions.path');
    }

    public function whereKey($key)
    {
        $directory = $this->directory() . '/' . $key;

        $files = Folder::getFiles($directory);

        return collect($files)->reject(function ($path) {
            return Str::endsWith($path, 'working.yaml');
        })->map(function ($path) use ($key) {
            return $this
                ->makeRevisionFromFile($key, $path)
                ->date(Carbon::createFromTimestamp(pathinfo($path, PATHINFO_FILENAME)));
        });
    }

    public function findWorkingCopyByKey($key)
    {
        $path = $this->directory() . '/' . $key . '/working.yaml';

        if (! File::exists($path)) {
            return null;
        }

        return $this->makeRevisionFromFile($key, $path);
    }

    public function save(Revision $revision)
    {
        File::put($revision->path(), $revision->fileContents());
    }

    protected function makeRevisionFromFile($key, $path)
    {
        $yaml = YAML::parse(File::get($path));

        return (new Revision)
            ->key($key)
            ->user($yaml['user'] ?? false)
            ->message($yaml['message'] ?? false)
            ->attributes($yaml['attributes']);
    }
}
