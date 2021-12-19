<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Statamic\Assets\Asset;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Fields\Value;
use Statamic\Globals\Variables;
use Statamic\Sites\Site;

class RuntimeValueCache
{
    protected static $runtimeValueCache = [];

    protected static function getAugmentableKey($augmentable)
    {
        if ($augmentable instanceof Entry) {
            return '_entry:'.$augmentable->id();
        } elseif ($augmentable instanceof Variables) {
            return '_variables:'.$augmentable->path();
        } elseif ($augmentable instanceof Asset) {
            return '_asset:'.$augmentable->path();
        } elseif ($augmentable instanceof Site) {
            return '_site:'.$augmentable->handle();
        } elseif ($augmentable instanceof Collection) {
            return '_entries:'.$augmentable->handle().':'.$augmentable->routes().$augmentable->template().$augmentable->title();
        }

        return null;
    }

    public static function resetRuntimeCache()
    {
        self::$runtimeValueCache = [];
    }

    protected static function getRawKey($value)
    {
        if (is_object($value)) {
            return spl_object_hash($value);
        } elseif (is_string($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return (string) $value;
        } elseif (is_numeric($value)) {
            return (string) $value;
        }

        return serialize($value);
    }

    public static function getAugmentableValue(Augmentable $augmentable)
    {
        $augmentKey = self::getAugmentableKey($augmentable);

        if ($augmentKey != null) {
            if (! array_key_exists($augmentKey, self::$runtimeValueCache)) {
                self::$runtimeValueCache[$augmentKey] = $augmentable->toAugmentedArray();
            }

            return self::$runtimeValueCache[$augmentKey];
        }

        return $augmentable->toAugmentedArray();
    }

    public static function getValue(Value $value)
    {
        $handle = $value->handle();
        $augmentable = $value->augmentable();
        $augmentKey = self::getAugmentableKey($augmentable);

        if ($augmentKey != null) {
            $cacheKey = '_cache:'.$augmentKey.':'.$handle.spl_object_hash($augmentable).self::getRawKey($value->raw());

            if (! array_key_exists($cacheKey, self::$runtimeValueCache)) {
                self::$runtimeValueCache[$cacheKey] = $value->value();
            }

            return self::$runtimeValueCache[$cacheKey];
        }

        return $value->value();
    }
}
