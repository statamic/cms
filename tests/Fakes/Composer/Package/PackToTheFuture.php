<?php

namespace Tests\Fakes\Composer\Package;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;

class PackToTheFuture
{
    const DEFAULT_TEST_PACKAGE = 'test/package';
    const DEFAULT_TEST_PACKAGE_LOCATION = __DIR__.'/../../../Composer/__fixtures__/test-package';

    /**
     * Set version on default test package.
     */
    public static function setVersion(string $version)
    {
        static::generateComposerJson(static::DEFAULT_TEST_PACKAGE, $version);
    }

    /**
     * Set addon package name and version on our test package.
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
     * @param  string|null  $version
     */
    public static function generateComposerJson(string $package, string $version, array $extra = [], $path = null)
    {
        $content = array_merge([
            'name' => $package,
            'version' => $version,
        ], $extra);

        file_put_contents(
            static::preparePath($path ?? static::DEFAULT_TEST_PACKAGE_LOCATION.'/composer.json'),
            json_encode($content, JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Generate composer.lock file for our test package.
     *
     * @param  string|null  $path
     */
    public static function generateComposerLock(string $package, string $version, $path = null, $dev = false)
    {
        $packagesKey = $dev ? 'packages-dev' : 'packages';
        $nonFavouritePackagesKey = $dev ? 'packages' : 'packages-dev';

        $content = [
            $packagesKey => [
                [
                    'name' => $package,
                    'version' => $version,
                ],
            ],
            $nonFavouritePackagesKey => [],
        ];

        file_put_contents(
            static::preparePath($path ?? static::DEFAULT_TEST_PACKAGE_LOCATION.'/composer.lock'),
            json_encode($content, JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Generate composer.lock file for multiple test packages.
     *
     * @param  string  $packages
     * @param  string|null  $path
     */
    public static function generateComposerLockForMultiple($packages, $path = null)
    {
        $packages = collect($packages)
            ->map(function ($version, $package) {
                return [
                    'name' => $package,
                    'version' => $version,
                ];
            })
            ->values()
            ->all();

        $content = [
            'packages' => $packages,
            'packages-dev' => [],
        ];

        file_put_contents(
            static::preparePath($path ?? static::DEFAULT_TEST_PACKAGE_LOCATION.'/composer.lock'),
            json_encode($content, JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Prepare path.
     *
     * @param  string  $path
     * @param string
     */
    private static function preparePath($path)
    {
        $files = app('files');
        $folder = preg_replace('/(.*)\/[^\/]+\.[^\/]+/', '$1', Path::resolve($path));

        if (! $files->exists($folder)) {
            $files->makeDirectory($folder, 0755, true);
        }

        return $path;
    }
}
