<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

class TypeCoercion
{
    public static function coerceType($value)
    {
        if ($value == intval($value)) {
            return intval($value);
        } elseif ($value == floatval($value)) {
            return floatval($value);
        }

        return $value;
    }

    public static function coerceBool($value)
    {
        if (is_string($value)) {
            if ($value === 'true') {
                return true;
            }

            if ($value === 'false') {
                return false;
            }
        }

        return (bool) $value;
    }
}
