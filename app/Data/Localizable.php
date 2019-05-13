<?php

namespace Statamic\Data;

use Closure;
use Statamic\API\Site;
use Statamic\Contracts\Data\Localization;
use Statamic\Exceptions\InvalidLocalizationException;

trait Localizable
{
    protected $localizations;

    public function localizations()
    {
        return collect($this->localizations);
    }

    public function addLocalization(Localization $localization)
    {
        $localization->localizable($this);

        $this->localizations = $this->localizations()->put($localization->locale(), $localization);

        return $this;
    }

    public function removeLocalization(Localization $localization)
    {
        $this->localizations = $this->localizations()->forget($localization->locale());

        return $this;
    }

    public function existsIn($site)
    {
        return $this->localizations()->has($site);
    }

    public function in($site, $callback = null)
    {
        if ($site instanceof Closure || $callback instanceof Closure) {
            return $this->makeAndAddLocalization($site, $callback);
        }

        if ($this->existsIn($site)) {
            return $this->localizations()->get($site);
        }

        throw new InvalidLocalizationException("Entry is not localized into the [$site] site");
    }

    abstract protected function makeLocalization();

    public function makeAndAddLocalization($site, $callback = null)
    {
        if (! $callback) {
            $callback = $site;
            $site = Site::current()->handle();
        }

        $entry = $this->makeLocalization()->id($this->id)->locale($site);

        $this->addLocalization($entry);

        $callback($entry);

        return $entry;
    }

    public function inOrClone($site, $from = null)
    {
        try {
            return $this->in($site);
        } catch (InvalidLocalizationException $e) {
            $existing = clone $this->localizations()
                ->get($from ?? $this->localizations()->keys()->first());

            return $existing
                ->initialPath(null)
                ->locale($site);
        }
    }

    protected function forCurrentSite()
    {
        return $this->in(Site::current()->handle());
    }

    public function toArray()
    {
        return $this->forCurrentSite()->toArray();
    }

    public function __call($method, $args = [])
    {
        if (method_exists($this->makeLocalization(), $method)) {
            return call_user_func_array([$this->forCurrentSite(), $method], $args);
        }

        throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }
}
