<?php

namespace Statamic\Licensing;

use Illuminate\Support\Carbon;
use Illuminate\Support\MessageBag;
use Statamic\Events\LicensesRefreshed;
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
            ? (int) Carbon::createFromTimestamp($this->response('expiry'), config('app.timezone'))->diffInSeconds(absolute: true)
            : null;
    }

    public function requestValidationErrors()
    {
        return new MessageBag($this->response('error') === 422 ? $this->response('errors') : []);
    }

    public function outpostIsOffline()
    {
        return $this->requestErrorCode() >= 500 && $this->requestErrorCode() < 600;
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
        return $this->statamicValid() && $this->addonsValid();
    }

    public function invalid()
    {
        return ! $this->valid();
    }

    public function statamicValid()
    {
        return $this->statamic()->valid();
    }

    public function addonsValid()
    {
        return $this->addons()->reject->valid()->isEmpty();
    }

    public function onlyStatamicIsInvalid()
    {
        return $this->addonsValid() && ! $this->statamicValid();
    }

    public function onlyAddonsAreInvalid()
    {
        return $this->statamicValid() && ! $this->addonsValid();
    }

    public function statamicNeedsRenewal()
    {
        return $this->statamic()->needsRenewal();
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

        LicensesRefreshed::dispatch();
    }

    public function usingLicenseKeyFile()
    {
        return $this->outpost->usingLicenseKeyFile();
    }

    public function licensingAlert()
    {
        if ($this->outpostIsOffline() || $this->requestFailed() || ! $this->invalid()) {
            return null;
        }

        return [
            'testing' => $isTestDomain = $this->isOnTestDomain(),
            'message' => $this->invalidLicenseMessage($isTestDomain),
        ];
    }

    public function requestFailureMessage()
    {
        if ($this->usingLicenseKeyFile()) {
            return __('statamic::messages.outpost_license_key_error');
        }

        if ($this->requestErrorCode() === 422) {
            return __('statamic::messages.outpost_error_422').' '.
                implode(' ', $this->requestValidationErrors()->unique());
        }

        if ($this->requestErrorCode() === 429) {
            return __('statamic::messages.outpost_error_429').' '.
                trans_choice('statamic::messages.try_again_in_seconds', $this->failedRequestRetrySeconds());
        }

        return __('statamic::messages.outpost_issue_try_later');
    }

    private function invalidLicenseMessage($isTestDomain)
    {
        if ($isTestDomain) {
            if ($this->onlyAddonsAreInvalid()) {
                return __('statamic::messages.licensing_trial_mode_alert_addons');
            }

            if ($this->onlyStatamicIsInvalid()) {
                return __('statamic::messages.licensing_trial_mode_alert_statamic');
            }

            return __('statamic::messages.licensing_trial_mode_alert');
        }

        if ($this->onlyAddonsAreInvalid()) {
            return __('statamic::messages.licensing_production_alert_addons');
        }

        if ($this->onlyStatamicIsInvalid()) {
            if ($this->statamicNeedsRenewal()) {
                return __('statamic::messages.licensing_production_alert_renew_statamic');
            }

            return __('statamic::messages.licensing_production_alert_statamic');
        }

        return __('statamic::messages.licensing_production_alert');
    }
}
