<?php

namespace Statamic\Licensing;

use Statamic\Support\Arr;

abstract class License
{
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function response()
    {
        return $this->response;
    }

    public function valid()
    {
        return Arr::get($this->response, 'valid');
    }

    public function invalidReason()
    {
        if (! $reason = Arr::get($this->response, 'reason')) {
            return;
        }

        return trans('statamic::messages.licensing_error_'.$reason);
    }
}
