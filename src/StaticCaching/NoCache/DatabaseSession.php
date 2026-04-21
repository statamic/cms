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
        $regions = DatabaseRegion::where('url', md5($this->url))->get(['key']);

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

        // Fall back to treating the value as a raw serialized string for rows
        // written before base64 encoding was introduced. Strict base64_decode
        // reliably detects legacy rows because any serialized PHP value always
        // contains ":" or ";", neither of which are in the base64 alphabet.
        $decoded = base64_decode($region->region, true);

        if ($decoded === false) {
            $decoded = $region->region;
        }

        return unserialize($decoded, ['allowed_classes' => true]);
    }

    protected function cacheRegion(Region $region)
    {
        DatabaseRegion::updateOrCreate([
            'key' => $region->key(),
        ], [
            'url' => md5($this->url),
            'region' => base64_encode(serialize($region)),
        ]);
    }
}
