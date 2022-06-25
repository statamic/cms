<?php

namespace Statamic\StaticCaching\NoCache;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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

    public function getUrl()
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
    public function getRegions(): Collection
    {
        return $this->regions;
    }

    public function region(string $key): Region
    {
        return $this->regions[$key];
    }

    public function pushRegion($contents, $context, $extension): StringRegion
    {
        $region = new StringRegion($this, trim($contents), $context, $extension);

        return $this->regions[$region->key()] = $region;
    }

    public function pushView($view, $context): ViewRegion
    {
        $region = new ViewRegion($this, $view, $context);

        return $this->regions[$region->key()] = $region;
    }

    public function getCascade()
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
}
