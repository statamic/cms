<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Exception;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\BulkAugmentor;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class RuntimeValues
{
    public static function resolveWithRuntimeIsolation($augmentable)
    {
        GlobalRuntimeState::$requiresRuntimeIsolation = true;
        try {
            if ($augmentable instanceof Collection && $augmentable->first() instanceof Augmentable) {
                $value = BulkAugmentor::make($augmentable)->toArray();
            } else {
                $value = $augmentable->toDeferredAugmentedArray();
            }
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
