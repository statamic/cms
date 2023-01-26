<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Exception;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class RuntimeValues
{
    public static function resolveWithRuntimeIsolation($augmentable)
    {
        GlobalRuntimeState::$requiresRuntimeIsolation = true;
        try {
            $value = $augmentable->toAugmentedArray();
        } catch (Exception $e) {
            throw $e;
        } finally {
            GlobalRuntimeState::$requiresRuntimeIsolation = false;
        }

        return $value;
    }

    public static function getValue(Value $value)
    {
        GlobalRuntimeState::$requiresRuntimeIsolation = true;
        try {
            $value = $value->value();
        } catch (Exception $e) {
            throw $e;
        } finally {
            GlobalRuntimeState::$requiresRuntimeIsolation = false;
        }

        return $value;
    }
}
