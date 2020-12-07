<?php

namespace Tests;

use PHPUnit\Framework\Assert;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $shouldFakeVersion = true;
    protected $shouldPreventNavBeingBuilt = true;

    protected function setUp(): void
    {
        require_once __DIR__.'/ConsoleKernel.php';

        parent::setUp();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventSavingStacheItemsToDisk::class])) {
            $this->preventSavingStacheItemsToDisk();
        }

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.0.0-testing');
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        if ($this->shouldPreventNavBeingBuilt) {
            \Statamic\Facades\CP\Nav::shouldReceive('build')->andReturn([]);
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        $this->addGqlMacros();
    }

    public function tearDown(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventSavingStacheItemsToDisk::class])) {
            $this->deleteFakeStacheDirectory();
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Statamic\Providers\StatamicServiceProvider::class,
            \Rebing\GraphQL\GraphQLServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return ['Statamic' => 'Statamic\Statamic'];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../config/{$config}.php"));
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        // We changed the default sites setup but the tests assume defaults like the following.
        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/'],
            ],
        ]);
        $app['config']->set('auth.providers.users.driver', 'statamic');
        $app['config']->set('statamic.stache.watcher', false);
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.users', [
            'class' => \Statamic\Stache\Stores\UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('statamic.stache.stores.taxonomies.directory', __DIR__.'/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.terms.directory', __DIR__.'/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.navigation.directory', __DIR__.'/__fixtures__/content/navigation');
        $app['config']->set('statamic.stache.stores.globals.directory', __DIR__.'/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__.'/__fixtures__/content/assets');

        $app['config']->set('statamic.api.enabled', true);

        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('cache.stores.outpost', [
            'driver' => 'file',
            'path' => storage_path('framework/cache/outpost-data'),
        ]);
    }

    protected function assertEveryItem($items, $callback)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $passes = 0;

        foreach ($items as $item) {
            if ($callback($item)) {
                $passes++;
            }
        }

        $this->assertEquals(count($items), $passes, 'Failed asserting that every item passes.');
    }

    protected function assertEveryItemIsInstanceOf($class, $items)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $matches = 0;

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $matches++;
            }
        }

        $this->assertEquals(count($items), $matches, 'Failed asserting that every item is an instance of '.$class);
    }

    protected function assertFileEqualsString($filename, $expected)
    {
        $this->assertFileExists($filename);

        $this->assertEquals($expected, file_get_contents($filename));
    }

    protected function assertContainsHtml($string)
    {
        preg_match('/<[^<]+>/', $string, $matches);

        $this->assertNotEmpty($matches, 'Failed asserting that string contains HTML.');
    }

    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        $class = version_compare(app()->version(), 7, '>=') ? \Illuminate\Testing\Assert::class : \Illuminate\Foundation\Testing\Assert::class;
        $class::assertArraySubset($subset, $array, $checkForObjectIdentity, $message);
    }

    protected function isRunningWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    // This method is unavailable on earlier versions of Laravel.
    public function partialMock($abstract, \Closure $mock = null)
    {
        $mock = \Mockery::mock(...array_filter(func_get_args()))->makePartial();
        $this->app->instance($abstract, $mock);

        return $mock;
    }

    /**
     * @deprecated
     */
    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        method_exists(static::class, 'assertFileDoesNotExist')
            ? static::assertFileDoesNotExist($filename, $message)
            : parent::assertFileNotExists($filename, $message);
    }

    /**
     * @deprecated
     */
    public static function assertDirectoryNotExists(string $filename, string $message = ''): void
    {
        method_exists(static::class, 'assertDirectoryDoesNotExist')
            ? static::assertDirectoryDoesNotExist($filename, $message)
            : parent::assertDirectoryNotExists($filename, $message);
    }

    private function addGqlMacros()
    {
        $testResponseClass = version_compare($this->app->version(), 7, '<')
            ? \Illuminate\Foundation\Testing\TestResponse::class
            : \Illuminate\Testing\TestResponse::class;

        $testResponseClass::macro('assertGqlOk', function () {
            $this->assertOk();

            $json = $this->json();

            if (isset($json['errors'])) {
                throw new \PHPUnit\Framework\ExpectationFailedException(
                    'GraphQL response contained errors',
                    new \SebastianBergmann\Comparator\ComparisonFailure('', '', '', json_encode($json, JSON_PRETTY_PRINT))
                );
            }

            return $this;
        });
    }
}
