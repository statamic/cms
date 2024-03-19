<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Arr;
use Statamic\Statamic;

class BladeTagsDirective
{
    public static function handle($tags): array
    {
        $variables = [];

        foreach (Arr::wrap($tags) as $key => $value) {
            if (is_array($value) && count($value) > 0) {
                $tag = array_keys($value)[0];
                $params = array_values($value)[0];
            } else if (is_string($value)) {
                $tag = $value;
                $params = [];
            } else {
                continue;
            }

            $varName = is_string($key) ? $key : camel_case(str_replace(':', '_', $tag));
            $variables[$varName] = Statamic::tag($tag)->params($params)->fetch();
        }

        return $variables;
    }
}
