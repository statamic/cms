<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

class RuntimeHelpers
{
    public static function factorial($number)
    {
        $result = 1;

        for ($i = 1; $i <= $number; $i++) {
            $result = $result * $i;
        }

        return $result;
    }

    public static function iterativeFactorial($number, $iterations)
    {
        $result = 1;

        for ($i = 1; $i <= $iterations; $i++) {
            if ($i == 1) {
                $result = self::factorial($number);
            } else {
                $result = self::factorial($result);
            }
        }

        return $result;
    }
}
