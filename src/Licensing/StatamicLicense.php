<?php

namespace Statamic\Licensing;

use Statamic\Statamic;
use Statamic\Support\Arr;

class StatamicLicense extends License
{
    public function pro()
    {
        return Statamic::pro();
    }

    public function version()
    {
        return Statamic::version();
    }

    public function needsRenewal()
    {
        return Arr::get($this->response, 'reason') === 'outside_license_range';
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
