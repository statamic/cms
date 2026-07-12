<?php

namespace Tests\Antlers\Fixtures\Addon\Modifiers;

use Statamic\Modifiers\Modifier;

class VarTestModifier extends Modifier
{
    protected static $handle = 'var_test_modifier';

    public static $value = null;
    public static $params = null;

    public function index($value, $params)
    {
        self::$value = $value;
        self::$params = $params;
    }
}
