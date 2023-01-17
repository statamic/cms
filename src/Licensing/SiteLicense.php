<?php

namespace Statamic\Licensing;

use Statamic\Support\Arr;

class SiteLicense extends License
{
    public function key()
    {
        return config('statamic.system.license_key');
    }

    public function usesIncorrectKeyFormat()
    {
        return ! preg_match('/^[a-zA-Z0-9]{16}$/', $this->key());
    }

    public function hasDomains()
    {
        return $this->domains()->isNotEmpty();
    }

    public function hasMultipleDomains()
    {
        return $this->domains()->count() > 1;
    }

    public function additionalDomainCount()
    {
        return $this->hasMultipleDomains() ? $this->domains()->count() - 1 : 0;
    }

    public function domain()
    {
        if (! $this->hasDomains()) {
            return null;
        }

        return $this->domains()->first();
    }

    public function domains()
    {
        return collect(Arr::get($this->response, 'domains'));
    }

    public function url()
    {
        $url = 'https://statamic.com/account/sites';

        if ($key = $this->key()) {
            $url .= '/'.$key;
        } else {
            $url .= '/create';
        }

        return $url;
    }
}
