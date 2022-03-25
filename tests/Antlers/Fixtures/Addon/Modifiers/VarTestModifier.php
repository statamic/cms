<?php

namespace Tests\Antlers\Fixtures\Addon\Modifiers;

use Statamic\Modifiers\Modifier;

class VarTestModifier extends Modifier
{
    protected static $handle = 'var_test_modifier';

    public static $value = null;

    public function index($value)
    {
        self::$value = $value;
    }
}
