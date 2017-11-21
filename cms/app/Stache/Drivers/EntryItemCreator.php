<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Term;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Pattern;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;

class EntryItemCreator
{
    /**
     * @var \Statamic\Stache\Stache
     */
    private $stache;

    /**
     * @var Collection
     */
    private $files;

    /**
     * @var Collection
     */
    private $items;


    public function __construct(Stache $stache, $files)
    {
        $this->stache = $stache;
        $this->files = $files->first(); // default locale only
        $this->items = collect();
    }

    /**
     * Create/get all the entries sorted into collections
     *
     * @return Collection
     */
    public function create()
    {
        return $this->files->map(function ($contents, $path) {
            // We only want files from the default locale.
            if (substr_count($path, '/') > 2) {
                return null;
            }

            $collection = explode('/', $path)[1];
            $item = $this->createEntry($contents, $path, $collection);
            return compact('item', 'path', 'collection');
        })->filter()->values()->groupBy('collection');
    }

    private function createEntry($contents, $path, $collection)
    {
        return Entry::create(pathinfo(Path::clean($path))['filename'])
            ->collection($collection)
            ->with(YAML::parse($contents))
            ->published(app('Statamic\Contracts\Data\Content\StatusParser')->entryPublished($path))
            ->order(app('Statamic\Contracts\Data\Content\OrderParser')->getEntryOrder($path))
            ->get();
    }
}
