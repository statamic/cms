<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Revision;
use Statamic\Facades\YAML;
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

        return Revision::makeRevisionFromArray($key, $yaml);
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
            File::delete($item->path()); // windows fix - deleteItemFromDisk didnt work

            return;
        }

        return parent::delete($item);
    }
}
