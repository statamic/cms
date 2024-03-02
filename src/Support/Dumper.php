<?php

namespace Statamic\Support;

use Statamic\Fields\Value;

class Dumper
{
    public static function materializeValues($values)
    {
        if ($values instanceof Value) {
            return $values;
        }

        if (is_array($values)) {
            $values = collect($values)->mapWithKeys(function ($value, $key) {
                if ($value instanceof Value) {
                    $value = $value->materialize();
                }

                return [$key => $value];
            })->all();
        }

        return $values;
    }

    public static function dump($values)
    {
        dump(self::materializeValues($values));
    }

    public static function dd($values)
    {
        $values = self::materializeValues($values);

        function_exists('ddd') ? ddd($values) : dd($values);
    }
}
