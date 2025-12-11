<?php

namespace Statamic\StaticCaching\NoCache;

class DatabaseSession extends Session
{
    public function write()
    {
        // Nothing to write. Session gets compiled by querying regions.
    }

    public function restore()
    {
        $regions = DatabaseRegion::where('url', $this->url)->get(['key']);

        $this->regions = $regions->map->key;

        $this->cascade = $this->restoreCascade();

        $this->resolvePageAndPathForPagination();

        return $this;
    }

    public function region(string $key): Region
    {
        $region = DatabaseRegion::where('key', $key)->first();

        if (! $region) {
            throw new RegionNotFound($key);
        }

        return unserialize($region->region);
    }

    protected function cacheRegion(Region $region)
    {
        DatabaseRegion::updateOrCreate([
            'key' => $region->key(),
        ], [
            'url' => $this->url,
            'region' => serialize($region),
        ]);
    }
}
