<?php

namespace Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\User;
use Statamic\Statamic;
use Statamic\Support\Str;
use Tests\Fakes\FakeArtisanRequest;

class StatamicTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.cp.date_format', 'cp-date-format');
        $app['config']->set('statamic.system.date_format', 'system-date-format');

        Route::get('date-format', function () {
            return [
                'dateFormat' => Statamic::dateFormat(),
                'dateTimeFormat' => Statamic::dateTimeFormat(),
                'cpDateFormat' => Statamic::cpDateFormat(),
                'cpDateTimeFormat' => Statamic::cpDateTimeFormat(),
            ];
        });
    }

    /** @test */
    public function it_checks_for_cp_route()
    {
        $this->get('/not-cp');
        $this->assertFalse(Statamic::isCpRoute());

        $this->get('/cp');
        $this->assertTrue(Statamic::isCpRoute());

        $this->get('/cp/foo');
        $this->assertTrue(Statamic::isCpRoute());

        $this->get('/cpa');
        $this->assertFalse(Statamic::isCpRoute());

        config(['statamic.cp.enabled' => false]);
        $this->get('/cp/foo');
        $this->assertFalse(Statamic::isCpRoute());
    }

    /** @test */
    public function it_gets_the_system_date_format()
    {
        $this->assertEquals('system-date-format', Statamic::dateFormat());
    }

    /** @test */
    public function it_gets_the_cp_date_format()
    {
        $this->assertEquals('cp-date-format', Statamic::cpDateFormat());
    }

    /** @test */
    public function it_gets_the_users_preferred_date_format_when_requesting_cp_format_but_not_the_system_format()
    {
        $user = tap(User::make())->save();
        $user->setPreference('date_format', 'user-date-format');

        $response = $this->actingAs($user)->getJson('/date-format')->assertOk();

        $this->assertEquals('user-date-format', $response->json('cpDateFormat'));
        $this->assertEquals('system-date-format', $response->json('dateFormat'));
    }

    /** @test */
    public function it_appends_time_if_system_date_format_doesnt_have_time_in_it()
    {
        config(['statamic.system.date_format' => 'Y--m--d']);

        $this->assertEquals('Y--m--d H:i', Statamic::dateTimeFormat());
    }

    /**
     * @test
     *
     * @dataProvider formatsWithTime
     **/
    public function it_doesnt_append_time_if_system_date_format_already_has_time_in_it($format)
    {
        config(['statamic.system.date_format' => $format]);

        $this->assertEquals($format, Statamic::dateTimeFormat());
    }

    /** @test */
    public function it_appends_time_if_cp_date_format_doesnt_have_time_in_it()
    {
        config(['statamic.cp.date_format' => 'Y--m--d']);

        $this->assertEquals('Y--m--d H:i', Statamic::cpDateTimeFormat());
    }

    /**
     * @test
     *
     * @dataProvider formatsWithTime
     **/
    public function it_doesnt_append_time_if_cp_date_format_already_has_time_in_it($format)
    {
        config(['statamic.cp.date_format' => $format]);

        $this->assertEquals($format, Statamic::cpDateTimeFormat());
    }

    /** @test */
    public function it_wraps_fluent_tag_helper()
    {
        $this->assertInstanceOf(\Statamic\Tags\FluentTag::class, Statamic::tag('some_tag'));
    }

    /** @test */
    public function it_wraps_fluent_modifier_helper()
    {
        $this->assertInstanceOf(\Statamic\Modifiers\Modify::class, Statamic::modify('some_value'));
    }

    public function formatsWithTime()
    {
        return [
            '12-hour without leading zeros' => ['g'],
            '24-hour without leading zeros' => ['G'],
            '12-hour with leading zeros' => ['h'],
            '24-hour with leading zeros' => ['H'],
            'unix timestamp' => ['U'],
            'ISO 8601' => ['c'],
            'RFC 2822' => ['r'],
        ];
    }

    /** @test */
    public function it_aliases_query_builders()
    {
        app()->bind('statamic.queries.test', function () {
            return 'the test query builder';
        });

        $this->assertEquals('the test query builder', Statamic::query('test'));
    }

    /** @test */
    public function native_query_builder_aliases_are_bound()
    {
        $aliases = [
            'entries' => \Statamic\Stache\Query\EntryQueryBuilder::class,
            'terms' => \Statamic\Stache\Query\TermQueryBuilder::class,
            'assets' => \Statamic\Assets\QueryBuilder::class,
            'users' => \Statamic\Stache\Query\UserQueryBuilder::class,
        ];

        foreach ($aliases as $alias => $class) {
            $this->assertInstanceOf($class, Statamic::query($alias));
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_query_builder_alias()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Target class [statamic.queries.test] does not exist.');

        Statamic::query('test');
    }

    /** @test */
    public function scripts_will_automatically_be_versioned()
    {
        Statamic::script('test-a', 'test');

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test-a', $allScripts);

        $testScript = $allScripts['test-a'][0];

        $this->assertTrue(Str::startsWith($testScript, 'test.js?v='));
        // Check if the version is 16 characters long.
        $this->assertEquals(16, strlen(Str::of($testScript)->after('.js?v=')));
    }

    /** @test */
    public function styles_will_automatically_be_versioned()
    {
        Statamic::style('test-b', 'test');

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test-b', $allStyles);

        $testStyle = $allStyles['test-b'][0];

        $this->assertTrue(Str::startsWith($testStyle, 'test.css?v='));
        // Check if the version is 16 characters long.
        $this->assertEquals(16, strlen(Str::of($testStyle)->after('.css?v=')));
    }

    /** @test */
    public function scripts_can_be_passed_with_a_laravel_mix_version()
    {
        $path = 'test.js?id=some-random-laravel-mix-version';

        // We can't test the mix helper, so we emulate it by adding `?id=`, as this is
        // the versioning syntax provied by Laravel Mix.
        // Statamic::script('test', mix('your-path'));

        Statamic::script('test-c', $path);

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test-c', $allScripts);

        $testScript = $allScripts['test-c'][0];

        $this->assertEquals($testScript, $path);
    }

    /** @test */
    public function styles_can_be_passed_with_a_laravel_mix_version()
    {
        $path = 'test.css?id=some-random-laravel-mix-version';

        // We can't test the mix helper, so we emulate it by adding `?id=`, as this is
        // the versioning syntax provied by Laravel Mix.
        // Statamic::script('test', mix('your-path'));

        Statamic::style('test-d', $path);

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test-d', $allStyles);

        $testStyle = $allStyles['test-d'][0];

        $this->assertEquals($testStyle, $path);
    }

    /** @test */
    public function assets_with_equal_names_will_be_cached_differently()
    {
        Statamic::style('test-name', __DIR__.'/../resources/css/test-path-1.css');
        Statamic::style('test-name', __DIR__.'/../resources/css/test-path-2.css');

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertNotEquals($allStyles['test-name'][0], $allStyles['test-name'][1]);
    }

    /**
     * @test
     *
     * @dataProvider cpAssetUrlProvider
     */
    public function it_gets_a_cp_asset_url($url, $expected)
    {
        $this->assertEquals($expected, Statamic::cpAssetUrl($url));
    }

    public function cpAssetUrlProvider()
    {
        return [
            'slash' => ['/foo/bar.jpg', 'http://localhost/vendor/statamic/cp/foo/bar.jpg'],
            'no slash' => ['foo/bar.jpg', 'http://localhost/vendor/statamic/cp/foo/bar.jpg'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider vendorPackageAssetUrlProvider
     */
    public function it_gets_the_vendor_package_asset_url($arguments, $expected)
    {
        $this->assertEquals($expected, Statamic::vendorPackageAssetUrl(...$arguments));
    }

    public function vendorPackageAssetUrlProvider()
    {
        return [
            'package' => [['package', 'cp.js'], 'http://localhost/vendor/package/cp.js'],
            'package with type' => [['package', 'test.jpg', 'images'], 'http://localhost/vendor/package/images/test.jpg'],
            'statamic cp' => [['statamic/cp', 'cp.js'], 'http://localhost/vendor/statamic/cp/cp.js'],
            'vendor url no slash' => [['irrelevant', 'vendor/foo/bar.js'], 'http://localhost/vendor/foo/bar.js'],
            'vendor url with slash' => [['irrelevant', '/vendor/foo/bar.js'], 'http://localhost/vendor/foo/bar.js'],
        ];
    }

    /**
     * @test
     *
     * @define-env useFixtureTranslations
     **/
    public function it_makes_breadcrumbs()
    {
        // confirm the fake translations are being loaded
        $this->assertIsArray(__('messages'));

        $this->assertEquals('one ‹ messages ‹ two', Statamic::crumb('one', 'messages', 'two'));
    }

    public function useFixtureTranslations($app)
    {
        $app->useLangPath(__DIR__.'/__fixtures__/lang');
    }

    /** @test */
    public function it_can_detect_if_running_in_a_queue_worker()
    {
        // It should return false by default
        $this->assertFalse(Statamic::isWorker());

        // It should return false when being called from a custom command
        Request::swap(new FakeArtisanRequest('stache:clear'));
        $this->assertFalse(Statamic::isWorker());
        Request::swap(new FakeArtisanRequest('statamic:install'));
        $this->assertFalse(Statamic::isWorker());

        // It should return true when being called from any command beginning with `queue:`
        Request::swap(new FakeArtisanRequest('queue:listen'));
        $this->assertTrue(Statamic::isWorker());
        Request::swap(new FakeArtisanRequest('queue:work'));
        $this->assertTrue(Statamic::isWorker());
        Request::swap(new FakeArtisanRequest('horizon:work'));
        $this->assertTrue(Statamic::isWorker());

        // It should always return false when not running in console
        App::shouldReceive('runningInConsole')->andReturn(false);
        $this->assertFalse(Statamic::isWorker());
    }
}
