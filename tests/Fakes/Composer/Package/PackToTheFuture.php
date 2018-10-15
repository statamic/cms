<?php

namespace Tests\Fakes\Composer\Package;

use Illuminate\Support\Facades\File;

class PackToTheFuture
{
    const DEFAULT_TEST_PACKAGE = 'test/package';

    /**
     * Set version on default test package.
     *
     * @param string $version
     */
    public static function setVersion(string $version)
    {
        static::setPackage(static::DEFAULT_TEST_PACKAGE, $version);
    }

    /**
     * Set custom package name and version on our test package.
     *
     * @param string $package
     * @param string $version
     */
    public static function setPackage(string $package, string $version)
    {
        File::put(__DIR__.'/test-package/composer.json', json_encode([
            'name' => $package,
            'version' => $version,
        ], JSON_UNESCAPED_SLASHES));
    }
}
