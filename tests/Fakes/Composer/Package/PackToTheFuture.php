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
        static::generateComposerJson(static::DEFAULT_TEST_PACKAGE, $version);
    }

    /**
     * Set addon package name and version on our test package.
     *
     * @param string $package
     * @param string $version
     */
    public static function setAddon(string $package, string $version)
    {
        static::generateComposerJson($package, $version, [
            'autoload' => [
                'psr-4' => [
                    'Statamic\\Providers\\' => 'src',
                ],
            ],
            'extra' => [
                'statamic' => [
                    'name' => 'Example',
                    'description' => 'Example addon',
                ],
                'laravel' => [
                    'providers' => [
                        'Statamic\\Providers\\StatamicServiceProvider',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Generate composer.json file for our test package.
     *
     * @param string $package
     * @param string $version
     * @param array $extra
     */
    private static function generateComposerJson(string $package, string $version, array $extra = [])
    {
        $content = array_merge([
            'name' => $package,
            'version' => $version,
        ], $extra);

        File::put(__DIR__.'/../../../Composer/__fixtures__/test-package/composer.json', json_encode($content, JSON_UNESCAPED_SLASHES));
    }
}
