<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Collection;
use Statamic\Statamic;
use Statamic\Support\Str;

class TagsDirective
{
    public static function handle($tags): array
    {
        return Collection::wrap($tags)->mapWithKeys(function ($value, $key) {
            if (is_array($value) && count($value) > 0) {
                $tag = array_keys($value)[0];
                $params = array_values($value)[0];
            } elseif (is_string($value)) {
                $tag = $value;
                $params = [];
            }

            $var = is_string($key) ? $key : Str::camel(str_replace(':', '_', $tag));

            return [$var => Statamic::tag($tag)->params($params)->fetch()];
        })->all();
    }
}
