<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Arr;
use Statamic\Facades\Cascade;

class CascadeDirective
{
    public static function handle($keys): array
    {
        $data = Cascade::toArray();
        if (! $data) {
            dump('hydrating!');
            $data = Cascade::hydrate()->toArray();
        }

        return collect($keys)
            ->mapWithKeys(function ($default, $key) use ($data) {
                if (is_numeric($key)) {
                    $key = $default;
                    $default = null;
                    if (! array_key_exists($key, $data)) {
                        throw new \Exception("Key [{$key}] not found in cascade data.");
                    }
                }

                return [$key => Arr::get($data, $key, $default)];
            })
            ->all();
    }
}
