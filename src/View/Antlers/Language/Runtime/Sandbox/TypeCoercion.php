<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

class TypeCoercion
{
    public static function coerceType($value)
    {
        if (intval($value) == $value) {
            return intval($value);
        } elseif (floatval($value) == $value) {
            return floatval($value);
        }

        return $value;
    }
}
