<?php

namespace Statamic\Stache;

use Illuminate\Support\Collection;
use Statamic\API\Cache;
use Statamic\API\File;

class Persister
{
    /**
     * @var \Statamic\Stache\Stache
     */
    private $stache;

    /**
     * @var Collection
     */
    private $meta;

    /**
     * @var Collection
     */
    private $items;

    /**
     * @var Collection
     */
    private $keys;

    /**
     * @param \Statamic\Stache\Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->items = collect();
    }

    /**
     * Persist the Stache to Cache
     *
     * @param Collection $updates  Repos that need to be persisted
     */
    public function persist($updates)
    {
        // Get the meta from the stache
        $this->meta = collect($this->stache->meta());

        // Loop through all the updated repos and format their data according to
        // how their driver has specified it. Put the data into arrays
        // that we can loop over in a moment.
        $updates->unique()->each(function ($key) {
            $repo = $this->stache->repo($key);

            $arr = $this->stache->driver($key)->toPersistentArray($repo);

            $this->meta->put($key, $arr['meta']);

            if (isset($arr['items'])) {
                $this->items->put($key, $arr['items']);
            }
        });

        // Store meta data separately. This will be simple data that can
        // be loaded all the time with minimal overhead.
        $this->store('meta', $this->meta->all());

        // Keep track of the keys that will be persisting.
        $this->keys = collect(Cache::get('stache::keys', []));

        // Persist the taxonomies
        $this->store('taxonomies/data', $this->stache->taxonomies->toPersistableArray());
        $this->keys[] = 'taxonomies/data';

        // Loop through all the item objects which each driver has organized
        // into folders. These are separate because it has the potential to
        // be quite large. These will be lazy loaded to prevent overhead.
        $this->items->each(function ($folders, $key) {
            collect($folders)->each(function ($data, $folder) use ($key) {
                $keyed = $key . '/' . $folder;
                $this->store($keyed, $data);
                $this->keys->push($keyed);
            });
        });

        // Persist the keys
        Cache::put('stache::keys', $this->keys->unique()->all());
    }

    /**
     * Store the value
     *
     * @param string $key
     * @param mixed $value
     */
    private function store($key, $value)
    {
        Cache::put("stache::$key", $value);
    }
}
