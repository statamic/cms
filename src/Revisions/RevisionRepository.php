<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Statamic\Contracts\Revisions\Revision as RevisionContract;
use Statamic\Contracts\Revisions\RevisionRepository as Contract;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\YAML;
use Statamic\Support\FileCollection;
use Statamic\Support\Str;

class RevisionRepository implements Contract
{
    public function directory()
    {
        return config('statamic.revisions.path');
    }

    public function make(): RevisionContract
    {
        return new Revision;
    }

    public function whereKey($key)
    {
        $directory = $this->directory().'/'.$key;

        $files = Folder::getFiles($directory);

        return FileCollection::make($files)->filterByExtension('yaml')->reject(function ($path) {
            return Str::endsWith($path, 'working.yaml');
        })->map(function ($path) use ($key) {
            return $this->makeRevisionFromFile($key, $path);
        })->keyBy(function ($revision) {
            return $revision->date()->timestamp;
        });
    }

    public function findWorkingCopyByKey($key)
    {
        $path = $this->directory().'/'.$key.'/working.yaml';

        if (! File::exists($path)) {
            return null;
        }

        return $this->makeRevisionFromFile($key, $path);
    }

    public function save(RevisionContract $revision)
    {
        File::put($revision->path(), $revision->fileContents());

        $revision->id($revision->date()->timestamp);
    }

    public function delete(RevisionContract $revision)
    {
        File::delete($revision->path());
    }

    protected function makeRevisionFromFile($key, $path)
    {
        $yaml = YAML::parse(File::get($path));

        return (new Revision)
            ->key($key)
            ->action($yaml['action'] ?? false)
            ->id($date = $yaml['date'])
            ->date(Carbon::createFromTimestamp($date))
            ->user($yaml['user'] ?? false)
            ->message($yaml['message'] ?? false)
            ->attributes($yaml['attributes']);
    }
}
