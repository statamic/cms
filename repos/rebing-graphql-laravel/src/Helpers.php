<?php

declare(strict_types = 1);
namespace Rebing\GraphQL;

use Closure;
use OutOfBoundsException;

class Helpers
{
    /**
     * Originally from \Nuwave\Lighthouse\Support\Utils::applyEach
     *
     * Apply a callback to a value or each value in an array.
     *
     * @param mixed|array<mixed> $valueOrValues
     * @return mixed|array<mixed>
     */
    public static function applyEach(Closure $callback, $valueOrValues)
    {
        if (\is_array($valueOrValues)) {
            return array_map($callback, $valueOrValues);
        }

        return $callback($valueOrValues);
    }

    /**
     * Check compatible ability to use thecodingmachine/safe.
     *
     * @return string|false
     */
    public static function shouldUseSafe(string $methodName)
    {
        $packageName = 'thecodingmachine/safe';
        $safeVersion = \Composer\InstalledVersions::getVersion($packageName);

        if (!$safeVersion) {
            throw new OutOfBoundsException("Package {$packageName} is being replaced or provided but is not really installed");
        }

        $skipFunctions = [
            'uksort',
        ];

        // Version 2.
        if (version_compare($safeVersion, '2', '>=')) {
            if (\in_array(str_replace('\\Safe\\', '', $methodName), $skipFunctions)) {
                return false;
            }
        }

        if (!\is_callable($methodName)) {
            return false;
        }

        return $methodName;
    }
}
