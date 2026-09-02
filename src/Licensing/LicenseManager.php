<?php

namespace Statamic\Licensing;

use Illuminate\Support\Carbon;
use Illuminate\Support\MessageBag;
use Statamic\Events\LicensesRefreshed;
use Statamic\Support\Arr;

use function Statamic\trans as __;
use function Statamic\trans_choice;

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
            'hasSiteKey' => $this->hasSiteKey(),
            'sharedKey' => $this->site()->hasSharedKey(),
        ];
    }

    public function hasSiteKey(): bool
    {
        return (bool) $this->site()->key();
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
                $message = __('statamic::messages.licensing_trial_mode_alert_addons');
            } elseif ($this->onlyStatamicIsInvalid()) {
                $message = __('statamic::messages.licensing_trial_mode_alert_statamic');
            } else {
                $message = __('statamic::messages.licensing_trial_mode_alert');
            }
        } elseif ($this->onlyAddonsAreInvalid()) {
            $message = __('statamic::messages.licensing_production_alert_addons');
        } elseif ($this->onlyStatamicIsInvalid()) {
            $message = $this->statamicNeedsRenewal()
                ? __('statamic::messages.licensing_production_alert_renew_statamic')
                : __('statamic::messages.licensing_production_alert_statamic');
        } else {
            $message = __('statamic::messages.licensing_production_alert');
        }

        return $message.' '.$this->identityMessage();
    }

    public function primaryAction(): ?string
    {
        $site = $this->site();

        if (! $site->key()) {
            return 'mint';
        }

        if (! $site->isConnected()) {
            return 'connect';
        }

        if ($site->hasInvalidDomain()) {
            return 'domain';
        }

        if ($this->statamicNeedsRenewal() && $this->onlyStatamicIsInvalid()) {
            return 'renew';
        }

        return $this->invalid() ? 'buy' : null;
    }

    private function identityMessage(): string
    {
        if ($this->site()->hasSharedKey()) {
            return __('statamic::messages.licensing_shared_key');
        }

        if (! $this->hasSiteKey()) {
            return __('statamic::messages.licensing_site_key_missing');
        }

        if ($this->site()->isConnected() && $this->statamicNeedsRenewal()) {
            return __('statamic::messages.licensing_connected_needs_renewal');
        }

        return $this->site()->isConnected()
            ? __('statamic::messages.licensing_connected_unlicensed')
            : __('statamic::messages.licensing_not_connected');
    }
}
