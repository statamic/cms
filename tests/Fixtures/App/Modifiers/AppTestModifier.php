<?php

namespace App\Modifiers;

use Statamic\Modifiers\Modifier;

class AppTestModifier extends Modifier
{
    protected static $handle = 'app_test_modifier';

    public function index($value, $params, $context)
    {
        return strtoupper((string) $value).'-app-modifier';
    }
}
