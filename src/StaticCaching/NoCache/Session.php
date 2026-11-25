<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Facades\Cascade;
use Statamic\Facades\Data;
use Statamic\Facades\StaticCache;

class Session
{
    protected $cascade = [];

    /**
     * @var Collection<Region>
     */
    protected $regions;

    protected $url;

    protected $paginationUrl;

    protected $pendingRegions = [];

    private $regionCount = 0;

    public function __construct($url)
    {
        $this->url = $url;
        $this->regions = new Collection;
    }

    public function url()
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function setPaginationUrl(string $url)
    {
        $this->paginationUrl = $url;

        return $this;
    }

    /**
     * @return Collection<Region>
     */
    public function regions(): Collection
    {
        return $this->regions->mapWithKeys(fn ($key) => [$key => $this->region($key)]);
    }

    public function regionKeys(): Collection
    {
        return $this->regions->values();
    }

    public function region(string $key): Region
    {
        if ($region = $this->pendingRegion($key)) {
            return $region;
        }

        if ($this->regions->contains($key) && ($region = StaticCache::cacheStore()->get('nocache::region.'.$key))) {
            if ($region instanceof Region) {
                return $region;
            }

            if (is_string($region)) {
                return unserialize($region, ['allowed_classes' => true]);
            }
        }

        throw new RegionNotFound($key);
    }

    public function getRegionId(): string
    {
        $this->regionCount += 1;

        return md5($this->url.'|'.$this->regionCount);
    }

    public function pushRegion($contents, $context, $extension): StringRegion
    {
        $region = new StringRegion($this, trim($contents), $context, $extension);

        $this->pendingRegions[$region->key()] = $region;

        $this->regions[] = $region->key();

        return $region;
    }

    public function pushView($view, $context): ViewRegion
    {
        $region = new ViewRegion($this, $view, $context);

        $this->pendingRegions[$region->key()] = $region;

        $this->regions[] = $region->key();

        return $region;
    }

    public function cascade()
    {
        return $this->cascade;
    }

    public function setCascade(array $cascade)
    {
        $this->cascade = $cascade;

        return $this;
    }

    public function reset()
    {
        $this->regions = new Collection;
        $this->cascade = [];
        $this->pendingRegions = [];
    }

    public function write()
    {
        if ($this->regions->isEmpty()) {
            return;
        }

        $this->storePendingRegions();

        $store = StaticCache::cacheStore();

        $store->forever('nocache::urls', collect($store->get('nocache::urls', []))->push($this->url)->unique()->all());

        $store->forever('nocache::session.'.md5($this->url), [
            'regions' => $this->regions,
        ]);
    }

    public function restore()
    {
        $session = StaticCache::cacheStore()->get('nocache::session.'.md5($this->url));

        $this->regions = $this->regions->merge($session['regions'] ?? [])->unique()->values();
        $this->cascade = $this->restoreCascade();

        $this->resolvePageAndPathForPagination();

        return $this;
    }

    protected function restoreCascade()
    {
        return Cascade::instance()
            ->withContent(Data::findByRequestUrl($this->url))
            ->hydrate()
            ->toArray();
    }

    protected function resolvePageAndPathForPagination(): void
    {
        AbstractPaginator::currentPathResolver(fn () => Str::before($this->url, '?'));

        AbstractPaginator::currentPageResolver(function () {
            return Str::of($this->paginationUrl ?? $this->url)->after('page=')->before('&')->__toString();
        });
    }

    protected function cacheRegion(Region $region)
    {
        StaticCache::cacheStore()->forever('nocache::region.'.$region->key(), serialize($region));
    }

    protected function pendingRegion(string $key): ?Region
    {
        if (! $region = $this->pendingRegions[$key] ?? null) {
            return null;
        }

        // Hand back a copy that has been through serialization, exactly like one read
        // back from the store, so rendering behaves the same before and after write().
        return unserialize(serialize($region), ['allowed_classes' => true]);
    }

    protected function storePendingRegions(): void
    {
        collect($this->pendingRegions)->each(fn (Region $region) => $this->cacheRegion($region));
    }
}
