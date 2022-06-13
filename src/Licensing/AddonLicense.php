<?php

namespace Statamic\Licensing;

use Statamic\Facades\Addon;
use Statamic\Support\Arr;

class AddonLicense extends License
{
    protected $package;
    protected $addon;

    public function __construct($package, $response)
    {
        parent::__construct($response);
        $this->package = $package;
        $this->addon = Addon::get($package);
    }

    public function name()
    {
        return $this->addon->name();
    }

    public function version()
    {
        return $this->addon->version();
    }

    public function versionLimit()
    {
        return $this->response['version_limit'] ?? null;
    }

    public function edition()
    {
        return $this->addon->edition();
    }

    public function existsOnMarketplace()
    {
        return $this->response['exists'];
    }

    public function invalidReason()
    {
        switch (Arr::get($this->response, 'reason')) {
            case 'outside_license_range':
                [$start, $end] = $this->response['range'];

                return trans('statamic::messages.licensing_error_outside_license_range', compact('start', 'end'));

            case 'invalid_edition':
                return trans('statamic::messages.licensing_error_invalid_edition', ['edition' => $this->response['edition']]);
        }

        return parent::invalidReason();
    }

    public function addon()
    {
        return $this->addon;
    }
}
