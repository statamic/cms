<?php

namespace Statamic\Data\Entries;

use Closure;
use Statamic\API\Site;
use Statamic\Exceptions\InvalidLocalizationException;
use Statamic\Contracts\Data\Entries\Entry as Contract;

class Entry implements Contract
{
    protected $id;
    protected $collection;
    protected $localizations;

    public function __construct()
    {
        $this->localizations = collect();
    }

    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    public function collection($collection = null)
    {
        if (is_null($collection)) {
            return $this->collection;
        }

        $this->collection = $collection;

        return $this;
    }

    public function collectionHandle()
    {
        return $this->collection->handle();
    }

    public function localizations()
    {
        return $this->localizations;
    }

    public function addLocalization(LocalizedEntry $entry)
    {
        $entry->entry($this);

        $this->localizations[$entry->locale()] = $entry;

        return $this;
    }

    public function removeLocalization(LocalizedEntry $entry)
    {
        $this->localizations->forget($entry->locale());

        return $this;
    }

    public function existsIn($site)
    {
        return $this->localizations->has($site);
    }

    public function in($site, $callback = null)
    {
        if ($site instanceof Closure || $callback instanceof Closure) {
            return $this->makeAndAddLocalization($site, $callback);
        }

        if ($this->existsIn($site)) {
            return $this->localizations->get($site);
        }

        throw new InvalidLocalizationException("Entry is not localized into the [$site] site");
    }

    protected function makeAndAddLocalization($site, $callback = null)
    {
        if (! $callback) {
            $callback = $site;
            $site = Site::current()->handle();
        }

        $entry = (new LocalizedEntry)->id($this->id)->locale($site);

        $this->addLocalization($entry);

        $callback($entry);

        return $entry;
    }

    public function inOrClone($site, $from = null)
    {
        try {
            return $this->in($site);
        } catch (InvalidLocalizationException $e) {
            return clone $this->localizations
                ->get($from ?? $this->localizations->keys()->first())
                ->initialPath(null)
                ->locale($site);
        }
    }

    protected function forCurrentSite()
    {
        return $this->in(Site::current()->handle());
    }

    public function __call($method, $args = [])
    {
        if (in_array($method, ['slug', 'get', 'has', 'data', 'order', 'published', 'blueprint'])) {
            return call_user_func_array([$this->forCurrentSite(), $method], $args);
        }

        throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }
}
