<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Carbon;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Revision;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class RevisionsStore extends BasicStore
{
    public function save($item)
    {
        parent::save($item);
    }

    public function getItemKey($item)
    {
        return $item->key().'/'.$item->id();
    }

    public function key()
    {
        return 'revisions';
    }

    public function makeItemFromFile($path, $contents)
    {
        $yaml = YAML::parse(File::get($path));
        $key = (string) Str::of(Path::tidy($path))->beforeLast('/')->after(Path::tidy($this->directory()));

        if (str_ends_with($path, 'working.yaml')) {
            $yaml['action'] = 'working';
        }

        return Revision::make()
            ->initialPath($path)
            ->key($key)
            ->action($yaml['action'] ?? false)
            ->id(($date = $yaml['date'] ?? null))
            ->date($date ? Carbon::createFromTimestamp($date, config('app.timezone')) : null)
            ->user($yaml['user'] ?? false)
            ->message($yaml['message'] ?? false)
            ->attributes($yaml['attributes'] ?? []);
    }
}
