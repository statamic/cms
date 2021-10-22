<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class ConvertLibrary extends RuntimeLibrary
{
    protected $name = 'convert';

    protected $exposedMethods = [
        'toFloat' => 1,
        'toInt' => 1,
        'toBool' => 1,
        'toString' => 1,
        'typeOf' => 1,
        'className' => 1
    ];

    public function toFloat($value)
    {
        return floatval($value);
    }

    public function toInt($value)
    {
        return intval($value);
    }

    public function toBool($value)
    {
        return boolval($value);
    }

    public function toString($value)
    {
        return strval($value);
    }

    public function typeOf($value)
    {
        return gettype($value);
    }

    public function className($value)
    {
        return get_class($value);
    }
}
