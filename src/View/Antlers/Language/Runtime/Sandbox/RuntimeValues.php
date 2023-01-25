<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class RuntimeValues
{
    public static function resolveWithRuntimeIsolation($augmentable)
    {
        GlobalRuntimeState::$requiresRuntimeIsolation = true;
        $value = $augmentable->toAugmentedArray();
        GlobalRuntimeState::$requiresRuntimeIsolation = false;

        return $value;
    }

    public static function getValue(Value $value)
    {
        GlobalRuntimeState::$requiresRuntimeIsolation = true;
        $value = $value->value();
        GlobalRuntimeState::$requiresRuntimeIsolation = false;

        return $value;
    }
}
