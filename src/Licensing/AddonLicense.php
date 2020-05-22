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

    public function existsOnMarketplace()
    {
        return $this->response['exists'];
    }

    public function invalidReason()
    {
        if (Arr::get($this->response, 'reason') === 'outside_license_range') {
            [$start, $end] = $this->response['range'];

            return trans('statamic::messages.licensing_error_outside_license_range', compact('start', 'end'));
        }

        return parent::invalidReason();
    }
}
