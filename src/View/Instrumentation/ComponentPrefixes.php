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

    /** @param  string[]|null  $prefixes */
    public static function matches(string $name, ?array $prefixes = null): bool
    {
        $name = strtolower($name);

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
    public static function merge(array $prefixes): array
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
