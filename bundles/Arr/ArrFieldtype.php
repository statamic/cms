<?php

namespace Statamic\Addons\Arr;

use Statamic\Addons\BundleFieldtype as Fieldtype;

class ArrFieldtype extends Fieldtype
{
    protected static $handle = 'array';

    public function preProcess($data)
    {
        return array_merge($this->blankKeyed(), $data ?? []);
    }

    public function process($data)
    {
        return collect($data)
            ->pluck('value', 'key')
            ->when($this->isKeyed(), function ($data) {
                return $data->filter();
            })
            ->all();
    }

    protected function isKeyed()
    {
        return (bool) $this->config('keys');
    }

    protected function blankKeyed()
    {
        return collect($this->config('keys'))
            ->map(function () {
                return null;
            })
            ->all();
    }
}
