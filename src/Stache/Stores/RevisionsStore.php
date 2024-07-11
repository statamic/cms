<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Carbon;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Revisions\Revision;
use Statamic\Revisions\WorkingCopy;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class RevisionsStore extends BasicStore
{
    public function getItemFilter(SplFileInfo $file)
    {
        $path = Path::tidy($file->getPathname());

        if (Str::endsWith($path, 'working.yaml')) {
            return false;
        }

        return $file->getExtension() === 'yaml';
    }

    public function key()
    {
        return 'revisions';
    }

    public function makeItemFromFile($path, $contents)
    {
        $yaml = YAML::parse(File::get($path));

        $key = (string) Str::of($path)->beforeLast('/')->after($this->directory());

        return (new Revision)
            ->key($key)
            ->action($yaml['action'] ?? false)
            ->id($date = $yaml['date'])
            ->date(Carbon::createFromTimestamp($date))
            ->user($yaml['user'] ?? false)
            ->message($yaml['message'] ?? false)
            ->attributes($yaml['attributes']);
    }

    public function save($item)
    {
        if ($item instanceof WorkingCopy) {
            $this->writeItemToDisk($item);

            return;
        }

        return parent::save($item);
    }

    public function delete($item)
    {
        if ($item instanceof WorkingCopy) {
            $this->deleteItemFromDisk($item);

            return;
        }

        return parent::delete($item);
    }
}
