<?php

namespace Statamic\View\Instrumentation;

/** @internal */
final class ComponentPrefixes
{
    const DEFAULTS = [
        'x-', 'x:',
        's-', 's:',
        'statamic-', 'statamic:',
    ];

    const ALL = self::DEFAULTS;

    /**
     * @param  string  $name
     * @param  string[]|null  $prefixes
     * @return bool
     */
    public static function matches($name, ?array $prefixes = null)
    {
        $name = strtolower((string) $name);

        foreach ($prefixes ?? self::DEFAULTS as $prefix) {
            if ($prefix !== '' && str_starts_with($name, strtolower($prefix))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string[]  $prefixes
     * @return string[]
     */
    public static function merge(array $prefixes)
    {
        $merged = self::DEFAULTS;

        foreach ($prefixes as $prefix) {
            if (is_string($prefix) && $prefix !== '' && ! in_array($prefix, $merged, true)) {
                $merged[] = $prefix;
            }
        }

        return $merged;
    }
}
