<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Arr;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\Facades\Cascade;

class CascadeDirective
{
    public static function handle($keys = null): array
    {
        if (! $data = Cascade::toArray()) {
            $data = Cascade::hydrate()->toArray();
        }

        if (! isset($keys)) {
            return $data;
        }

        return collect($keys)
            ->mapWithKeys(function ($default, $key) use ($data) {
                if (is_numeric($key)) {
                    $key = $default;
                    $default = null;
                    if (! array_key_exists($key, $data)) {
                        throw new CascadeDataNotFoundException($key);
                    }
                }

                return [$key => Arr::get($data, $key, $default)];
            })
            ->all();
    }
}
