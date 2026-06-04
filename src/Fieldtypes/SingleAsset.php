<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Asset;
use Statamic\Fieldtypes\Assets\Assets;
use Statamic\Support\Arr;

class SingleAsset extends Assets
{
    protected $component = 'assets';
    protected $selectable = false;

    public function preProcess($values)
    {
        if (is_null($values)) {
            return [];
        }

        return collect(Arr::wrap($values))->map(function ($value) {
            return $this->valueToId($value);
        })->filter()->values()->all();
    }

    public function process($data)
    {
        if (is_null($data) || $data === []) {
            return [];
        }

        return collect(Arr::wrap($data))->map(function ($id) {
            return Asset::findOrFail($id)->path();
        })->values()->all();
    }
}
