<?php

namespace Statamic\Licensing;

use Statamic\Facades\Config;
use Statamic\Support\Arr;

class SiteLicense extends License
{
    public function key()
    {
        return Config::getLicenseKey();
    }

    public function usesIncorrectKeyFormat()
    {
        $key = $this->key();

        if (! $key) {
            return false;
        }

        return ! preg_match('/^(?:site_[a-zA-Z0-9]{26}|[a-zA-Z0-9]{16})$/', $key);
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
        $url = rtrim(config('statamic.system.licensing_url', 'https://statamic.com'), '/').'/account/sites';

        if ($key = $this->key()) {
            $url .= '/'.$key;
        } else {
            $url .= '/create';
        }

        return $url;
    }

    public function handoffUrl(): ?string
    {
        if (! $key = $this->key()) {
            return null;
        }

        return rtrim(config('statamic.system.licensing_url', 'https://statamic.com'), '/').'/licensing/handoff?'.http_build_query(['key' => $key]);
    }

    public function hasSharedKey(): bool
    {
        return (bool) Arr::get($this->response, 'shared_key');
    }

    public function wasRotated(): bool
    {
        return (bool) Arr::get($this->response, 'key_rotated');
    }
}
