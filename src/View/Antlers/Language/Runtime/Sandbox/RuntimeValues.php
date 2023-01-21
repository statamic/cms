<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Statamic\Assets\Asset;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Fields\Value;
use Statamic\Globals\Variables;
use Statamic\Sites\Site;
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
