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

    /**
     * @return Collection<Region>
     */
    public function regions(): Collection
    {
        return $this->regions->mapWithKeys(fn ($key) => [$key => $this->region($key)]);
    }

    public function region(string $key): Region
    {
        if ($this->regions->contains($key) && ($region = StaticCache::cacheStore()->get('nocache::region.'.$key))) {
            return $region;
        }

        throw new RegionNotFound($key);
    }

    public function getRegionId(): string
    {
        $this->regionCount += 1;

        return md5($this->url.$this->regionCount);
    }

    public function pushRegion($contents, $context, $extension): StringRegion
    {
        $region = new StringRegion($this, trim($contents), $context, $extension);

        $this->cacheRegion($region);

        $this->regions[] = $region->key();

        return $region;
    }

    public function pushView($view, $context): ViewRegion
    {
        $region = new ViewRegion($this, $view, $context);

        $this->cacheRegion($region);

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
    }

    public function write()
    {
        if ($this->regions->isEmpty()) {
            return;
        }

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
            return Str::of($this->url)->after('page=')->before('&')->__toString();
        });
    }

    protected function cacheRegion(Region $region)
    {
        StaticCache::cacheStore()->forever('nocache::region.'.$region->key(), $region);
    }
}
