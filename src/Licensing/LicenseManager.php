<?php

namespace Statamic\Licensing;

use Illuminate\Support\Carbon;
use Illuminate\Support\MessageBag;
use Statamic\Support\Arr;

class LicenseManager
{
    protected $outpost;
    protected $addons;

    public function __construct(Outpost $outpost)
    {
        $this->outpost = $outpost;
    }

    public function requestFailed()
    {
        return (bool) $this->requestErrorCode();
    }

    public function requestErrorCode()
    {
        return $this->response('error');
    }

    public function requestRateLimited()
    {
        return $this->requestErrorCode() === 429;
    }

    public function failedRequestRetrySeconds()
    {
        return $this->requestRateLimited()
            ? Carbon::createFromTimestamp($this->response('expiry'))->diffInSeconds()
            : null;
    }

    public function requestValidationErrors()
    {
        return new MessageBag($this->response('error') === 422 ? $this->response('errors') : []);
    }

    public function isOnPublicDomain()
    {
        return $this->response('public');
    }

    public function isOnTestDomain()
    {
        return ! $this->isOnPublicDomain();
    }

    public function valid()
    {
        return $this->statamic()->valid()
            && $this->addons()->reject->valid()->isEmpty();
    }

    public function invalid()
    {
        return ! $this->valid();
    }

    public function response($key = null, $default = null)
    {
        $response = $this->outpost->response();

        return $key ? Arr::get($response, $key, $default) : $response;
    }

    public function site()
    {
        return new SiteLicense($this->response('site'));
    }

    public function statamic()
    {
        return new StatamicLicense($this->response('statamic'));
    }

    public function addons()
    {
        return $this->addons = $this->addons ?? collect($this->response('packages'))
            ->map(function ($response, $package) {
                return new AddonLicense($package, $response);
            });
    }

    public function refresh()
    {
        $this->outpost->clearCachedResponse();
    }
}
