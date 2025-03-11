<?php

namespace Statamic\Assets;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Data\Localization;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasOrigin;
use Statamic\Facades\Site;

class LocalizedAsset implements \ArrayAccess, Arrayable, Augmentable, Localization
{
    use HasAugmentedInstance, HasOrigin;

    public function __construct(
        protected $locale,
        protected $asset
    ) {
    }

    public function locale($locale = null)
    {
        if (func_num_args() === 1) {
            throw new \Exception('The locale cannot be set on a LocalizedAsset.');
        }

        return $this->locale;
    }

    public function defaultLocale()
    {
        return $this->container()->sites()->first();
    }

    public function get($key, $fallback = null)
    {
        return $this->data()->get($key, $fallback);
    }

    public function set($key, $value)
    {
        $data = $this->data();

        $data->put($key, $value);

        return $this->data($data);
    }

    public function has($key)
    {
        return $this->get($key) != null;
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->asset->dataForLocale($this->locale);
        }

        $this->asset->dataForLocale($this->locale, $data);

        return $this;
    }

    public function merge($data)
    {
        $this->data($this->data()->merge($data));

        return $this;
    }

    // todo: re-work this logic later...
    public function origin($origin = null)
    {
        if ($this->locale === $this->defaultLocale()) {
            return null;
        }

        return $this->asset->inDefaultLocale();
    }

    public function site()
    {
        return Site::get($this->locale);
    }

    public function asset()
    {
        return $this->asset;
    }

    public function editUrl(): string
    {
        return $this->asset->editUrl()."?site={$this->locale}";
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedAsset($this);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->asset->$name(...$arguments);
    }

    public function __get($key)
    {
        // HasOrigin::values() merges values from the `data` property, so we need to return it here.
        if ($key === 'data') {
            return $this->data();
        }

        return $this->asset->$key;
    }

    public function getOriginByString($origin)
    {
        return $this->asset->in($origin);
    }
}
