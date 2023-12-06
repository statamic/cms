<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Cascade;
use Statamic\Facades\Data;

class Session
{
    protected $cascade = [];

    /**
     * @var Collection<Region>
     */
    protected $regions;

    protected $url;

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
        if ($this->regions->contains($key) && ($region = Cache::get('nocache::region.'.$key))) {
            return $region;
        }

        throw new RegionNotFound($key);
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

        Cache::forever('nocache::urls', collect(Cache::get('nocache::urls', []))->push($this->url)->unique()->all());

        Cache::forever('nocache::session.'.md5($this->url), [
            'regions' => $this->regions,
        ]);
    }

    public function restore()
    {
        $session = Cache::get('nocache::session.'.md5($this->url));

        $this->regions = $this->regions->merge($session['regions'] ?? []);
        $this->cascade = $this->restoreCascade();

        return $this;
    }

    private function restoreCascade()
    {
        return Cascade::instance()
            ->withContent(Data::findByRequestUrl($this->url))
            ->hydrate()
            ->toArray();
    }

    private function cacheRegion(Region $region)
    {
        Cache::forever('nocache::region.'.$region->key(), $region);
    }
}
