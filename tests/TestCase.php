<?php

namespace Tests;

use Illuminate\Testing\Assert as IlluminateAssert;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Http\Middleware\CP\AuthenticateSession;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WindowsHelpers;

    protected $shouldFakeVersion = true;
    protected $shouldPreventNavBeingBuilt = true;
    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->withoutMiddleware(AuthenticateSession::class);

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventSavingStacheItemsToDisk::class])) {
            $this->preventSavingStacheItemsToDisk();
        }

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->zeroOrMoreTimes()->andReturn('3.0.0-testing');
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        if ($this->shouldPreventNavBeingBuilt) {
            \Statamic\Facades\CP\Nav::shouldReceive('build')->zeroOrMoreTimes()->andReturn(collect());
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        $this->addGqlMacros();

        // We changed the default sites setup but the tests assume defaults like the following.
        File::put(resource_path('sites.yaml'), YAML::dump([
            'en' => [
                'name' => 'English',
                'url' => 'http://localhost/',
                'locale' => 'en_US',
            ],
        ]));
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
            \Wilderborn\Partyline\ServiceProvider::class,
            \Archetype\ServiceProvider::class,
            \Spatie\LaravelRay\RayServiceProvider::class,
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
            'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require (__DIR__."/../config/{$config}.php"));
        }
    }

    protected function getEnvironmentSetUp($app)
    {
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
        $app['config']->set('statamic.stache.stores.global-variables.directory', __DIR__.'/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__.'/__fixtures__/content/assets');
        $app['config']->set('statamic.stache.stores.nav-trees.directory', __DIR__.'/__fixtures__/content/structures/navigation');
        $app['config']->set('statamic.stache.stores.collection-trees.directory', __DIR__.'/__fixtures__/content/structures/collections');
        $app['config']->set('statamic.stache.stores.form-submissions.directory', __DIR__.'/__fixtures__/content/submissions');

        $app['config']->set('statamic.api.enabled', true);
        $app['config']->set('statamic.graphql.enabled', true);
        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('cache.stores.outpost', [
            'driver' => 'file',
            'path' => storage_path('framework/cache/outpost-data'),
        ]);

        $app['config']->set('statamic.search.indexes.default.driver', 'null');

        $viewPaths = $app['config']->get('view.paths');
        $viewPaths[] = __DIR__.'/__fixtures__/views/';

        $app['config']->set('view.paths', $viewPaths);
    }

    protected function setSites($sites)
    {
        Site::setSites($sites);

        Config::set('statamic.system.multisite', Site::hasMultiple());
    }

    protected function setSiteValue($site, $key, $value)
    {
        Site::setSiteValue($site, $key, $value);

        Config::set('statamic.system.multisite', Site::hasMultiple());
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

    protected function assertContainsHtml($string)
    {
        preg_match('/<[^<]+>/', $string, $matches);

        $this->assertNotEmpty($matches, 'Failed asserting that string contains HTML.');
    }

    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        IlluminateAssert::assertArraySubset($subset, $array, $checkForObjectIdentity, $message);
    }

    // This method is unavailable on earlier versions of Laravel.
    public function partialMock($abstract, ?\Closure $mock = null)
    {
        $mock = \Mockery::mock(...array_filter(func_get_args()))->makePartial();
        $this->app->instance($abstract, $mock);

        return $mock;
    }

    private function addGqlMacros()
    {
        TestResponse::macro('assertGqlOk', function () {
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

        TestResponse::macro('assertGqlUnauthorized', function () {
            $this->assertOk();

            $json = $this->json();

            if (! isset($json['errors'])) {
                throw new \PHPUnit\Framework\ExpectationFailedException(
                    'GraphQL response contained no errors',
                    new \SebastianBergmann\Comparator\ComparisonFailure('', '', json_encode(['errors' => [['message' => 'Unauthorized']]], JSON_PRETTY_PRINT), json_encode($json, JSON_PRETTY_PRINT))
                );
            }

            Assert::assertTrue(
                collect($json['errors'])->map->message->contains('Unauthorized'),
                'No unauthorized error message in response'
            );

            return $this;
        });
    }

    public function __call($name, $arguments)
    {
        if ($name == 'assertStringEqualsStringIgnoringLineEndings') {
            return Assert::assertThat(
                $arguments[1],
                new StringEqualsStringIgnoringLineEndings($arguments[0]),
                $arguments[2] ?? ''
            );
        }

        throw new \BadMethodCallException("Method [$name] does not exist.");
    }
}
