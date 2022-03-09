<?php

namespace Statamic\Testing;

use Archetype\ServiceProvider;
use Closure;
use Facades\Statamic\Version;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Rebing\GraphQL\GraphQLServiceProvider;
use SebastianBergmann\Comparator\ComparisonFailure;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;

abstract class TestCase extends BaseTestCase
{
    use WindowsHelpers;

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
            Version::shouldReceive('get')->andReturn('3.0.0-testing');
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        if ($this->shouldPreventNavBeingBuilt) {
            Nav::shouldReceive('build')->andReturn([]);
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
            StatamicServiceProvider::class,
            GraphQLServiceProvider::class,
            \Wilderborn\Partyline\ServiceProvider::class,
            ServiceProvider::class,
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
            $app['config']->set("statamic.$config", require(__DIR__."/../../config/{$config}.php"));
        }

        $app['config']->set('statamic.antlers.version', 'runtime');
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
            'class' => UsersStore::class,
            'directory' => Fixture::path('users'),
        ]);

        $app['config']->set('statamic.stache.stores.taxonomies.directory', Fixture::path('content/taxonomies'));
        $app['config']->set('statamic.stache.stores.terms.directory', Fixture::path('content/taxonomies'));
        $app['config']->set('statamic.stache.stores.collections.directory', Fixture::path('content/collections'));
        $app['config']->set('statamic.stache.stores.entries.directory', Fixture::path('content/collections'));
        $app['config']->set('statamic.stache.stores.navigation.directory', Fixture::path('content/navigation'));
        $app['config']->set('statamic.stache.stores.globals.directory', Fixture::path('content/globals'));
        $app['config']->set('statamic.stache.stores.asset-containers.directory', Fixture::path('content/assets'));
        $app['config']->set('statamic.stache.stores.nav-trees.directory', Fixture::path('content/structures/navigation'));
        $app['config']->set('statamic.stache.stores.collection-trees.directory', Fixture::path('content/structures/collections'));

        $app['config']->set('statamic.api.enabled', true);
        $app['config']->set('statamic.graphql.enabled', true);
        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('cache.stores.outpost', [
            'driver' => 'file',
            'path' => storage_path('framework/cache/outpost-data'),
        ]);

        $viewPaths = $app['config']->get('view.paths');
        $viewPaths[] = Fixture::path('views');

        $app['config']->set('view.paths', $viewPaths);
    }

    public static function assertEquals($expected, $actual, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        $args = static::normalizeArgsForWindows(func_get_args());

        parent::assertEquals(...$args);
    }

    protected function assertEveryItem($items, $callback)
    {
        if ($items instanceof Collection) {
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
        if ($items instanceof Collection) {
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

    // This method is unavailable on earlier versions of Laravel.
    public function partialMock($abstract, Closure $mock = null)
    {
        $mock = Mockery::mock(...array_filter(func_get_args()))->makePartial();
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

    public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        method_exists(Assert::class, 'assertMatchesRegularExpression')
            ? parent::assertMatchesRegularExpression($pattern, $string, $message)
            : parent::assertRegExp($pattern, $string, $message);
    }

    private function addGqlMacros()
    {
        $testResponseClass = version_compare($this->app->version(), 7, '<')
            ? \Illuminate\Foundation\Testing\TestResponse::class
            : TestResponse::class;

        $testResponseClass::macro('assertGqlOk', function () {
            $this->assertOk();

            $json = $this->json();

            if (isset($json['errors'])) {
                throw new ExpectationFailedException(
                    'GraphQL response contained errors',
                    new ComparisonFailure('', '', '', json_encode($json, JSON_PRETTY_PRINT))
                );
            }

            return $this;
        });

        $testResponseClass::macro('assertGqlUnauthorized', function () {
            $this->assertOk();

            $json = $this->json();

            if (! isset($json['errors'])) {
                throw new ExpectationFailedException(
                    'GraphQL response contained no errors',
                    new ComparisonFailure('', '', json_encode(['errors' => [['message' => 'Unauthorized']]], JSON_PRETTY_PRINT), json_encode($json, JSON_PRETTY_PRINT))
                );
            }

            Assert::assertTrue(
                collect($json['errors'])->map->message->contains('Unauthorized'),
                'No unauthorized error message in response'
            );

            return $this;
        });
    }
}
