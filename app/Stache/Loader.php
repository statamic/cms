<?php

namespace Statamic\Stache;

use Statamic\API\Cache;

class Loader
{
    /**
     * @var \Statamic\Stache\Stache
     */
    private $stache;

    /**
     * StacheLoader constructor.
     *
     * @param \Statamic\Stache\Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * Load the stache with meta data
     *
     * @return void
     */
    public function load()
    {
        // Here we will load the Stache with metadata only. We intentionally don't want
        // to load the data objects into memory. We'll lazy load them. Performance++
        $meta = $this->fetch('meta');

        // If there's no cached meta data, that means we have a fresh Stache.
        if (! $meta) {
            throw new EmptyStacheException;
        }

        // Save the meta data array to the stache. The persister will want this.
        $this->stache->meta($meta);

        // Insert meta data into their respective Stache repos.
        // The $meta array contains meta arrays for each driver.
        collect($meta)->each(function ($data, $key) {
            $repo = $this->stache->repo($key);

            if ($this->stache->driver($key)->isLocalizable()) {
                $repo->setPathsForAllLocales(array_get($data, 'paths', []))
                     ->setUrisForAllLocales(array_get($data, 'uris', []));
            } else {
                $repo->setPaths(array_get($data, 'paths', []))
                     ->setUris(array_get($data, 'uris', []));
            }
        });

        $this->stache->taxonomies->load($this->fetch('taxonomies/data'));
    }

    /**
     * Fetch the value by key
     *
     * @param string $key
     * @return mixed
     */
    private function fetch($key)
    {
        return Cache::get("stache::$key");
    }
}
