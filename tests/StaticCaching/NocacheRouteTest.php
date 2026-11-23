<?php

namespace Tests\StaticCaching;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\NoCache\Session;
use Tests\FakesContent;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NocacheRouteTest extends TestCase
{
    use FakesContent;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_nocache_regions_via_a_route()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $this->createPage('test', ['with' => ['title' => 'Test']]);

        $session = new Session('http://localhost/test');
        $regionOne = $session->pushRegion('First {{ example_count }} {{ name }} {{ title }}', ['name' => 'Dustin'], 'antlers.html');
        $regionTwo = $session->pushRegion('Second {{ example_count }} {{ name }} {{ title }}', ['name' => 'Will'], 'antlers.html');
        $session->write();

        $this
            ->postJson('/!/nocache', ['url' => 'http://localhost/test'])
            ->assertOk()
            ->assertExactJson([
                'csrf' => csrf_token(),
                'regions' => [
                    $regionOne->key() => 'First 1 Dustin Test',
                    $regionTwo->key() => 'Second 2 Will Test',
                ],
            ]);
    }

    #[Test]
    public function it_skips_regions_missing_from_the_cache_store()
    {
        $this->createPage('test', ['with' => ['title' => 'Test']]);

        $session = new Session('http://localhost/test');
        $missing = $session->pushRegion('Missing', [], 'antlers.html');
        $surviving = $session->pushRegion('Surviving {{ name }}', ['name' => 'Dustin'], 'antlers.html');
        $session->write();

        StaticCache::cacheStore()->forget('nocache::region.'.$missing->key());

        $this
            ->postJson('/!/nocache', ['url' => 'http://localhost/test'])
            ->assertOk()
            ->assertExactJson([
                'csrf' => csrf_token(),
                'regions' => [
                    $surviving->key() => 'Surviving Dustin',
                ],
            ]);
    }

    #[Test]
    public function it_skips_regions_with_a_nested_region_missing_from_the_cache_store()
    {
        $this->createPage('test', ['with' => ['title' => 'Test']]);

        $session = new Session('http://localhost/test');
        $missing = $session->pushRegion('Missing', [], 'antlers.html');
        $session->pushRegion('<span class="nocache" data-nocache="'.$missing->key().'">NOCACHE_PLACEHOLDER</span>', [], 'antlers.html');
        $surviving = $session->pushRegion('Surviving', [], 'antlers.html');
        $session->write();

        StaticCache::cacheStore()->forget('nocache::region.'.$missing->key());

        $this
            ->postJson('/!/nocache', ['url' => 'http://localhost/test'])
            ->assertOk()
            ->assertExactJson([
                'csrf' => csrf_token(),
                'regions' => [
                    $surviving->key() => 'Surviving',
                ],
            ]);
    }

    #[Test]
    public function it_paginates_using_the_url_when_ignoring_query_strings()
    {
        config(['statamic.static_caching.ignore_query_strings' => true]);

        Collection::make('posts')->save();
        EntryFactory::collection('posts')->id('one')->data(['title' => 'One'])->create();
        EntryFactory::collection('posts')->id('two')->data(['title' => 'Two'])->create();

        $this->createPage('test', ['with' => ['title' => 'Test']]);

        $session = new Session('http://localhost/test');
        $region = $session->pushRegion('{{ collection:posts sort="title" paginate="1" as="posts" }}{{ posts }}{{ title }}{{ /posts }}{{ /collection:posts }}', [], 'antlers.html');
        $session->write();

        $this
            ->postJson('/!/nocache', ['url' => 'http://localhost/test?page=2'])
            ->assertOk()
            ->assertExactJson([
                'csrf' => csrf_token(),
                'regions' => [
                    $region->key() => 'Two',
                ],
            ]);
    }

    #[Test]
    #[DataProvider('unnormalizedPaginationUrlProvider')]
    public function it_paginates_using_the_normalized_url($cachedUrl, $requestedUrl)
    {
        Collection::make('posts')->save();
        EntryFactory::collection('posts')->id('one')->data(['title' => 'One'])->create();
        EntryFactory::collection('posts')->id('two')->data(['title' => 'Two'])->create();

        $this->createPage('test', ['with' => ['title' => 'Test']]);

        $session = new Session($cachedUrl);
        $region = $session->pushRegion('{{ collection:posts sort="title" paginate="1" as="posts" }}{{ posts }}{{ title }}{{ /posts }}{{ /collection:posts }}', [], 'antlers.html');
        $session->write();

        $this
            ->postJson('/!/nocache', ['url' => $requestedUrl])
            ->assertOk()
            ->assertExactJson([
                'csrf' => csrf_token(),
                'regions' => [
                    $region->key() => 'Two',
                ],
            ]);
    }

    public static function unnormalizedPaginationUrlProvider()
    {
        return [
            'unsorted keys' => ['http://localhost/test?page=2&zpage=9', 'http://localhost/test?zpage=9&page=2'],
            'encoded value' => ['http://localhost/test?page=2', 'http://localhost/test?page=%32'],
        ];
    }

    #[Test]
    public function url_is_required()
    {
        $this
            ->postJson('/!/nocache')
            ->assertJsonValidationErrorFor('url');
    }

    /**
     * @see https://github.com/statamic/cms/issues/10801
     */
    #[Test]
    #[DataProvider('csrfTokenRouteProvider')]
    public function it_sets_the_xsrf_token_cookie_without_requiring_a_csrf_token($url)
    {
        // CSRF tokens aren't verified when running unit tests.
        $this->app['env'] = 'production';

        $response = $this
            ->postJson($url, ['url' => 'http://localhost/test'])
            ->assertOk();

        $response->assertCookie('XSRF-TOKEN', $response->json('csrf'));
    }

    public static function csrfTokenRouteProvider()
    {
        return [
            'nocache' => ['/!/nocache'],
            'csrf' => ['/!/csrf'],
        ];
    }

    #[Test]
    public function the_script_routes_are_not_registered_unless_script_delivery_is_external()
    {
        // Defaults to "inline", so the routes shouldn't exist.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('statamic.nocache.js'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('statamic.csrf.js'));

        $this->get('/!/nocache.js')->assertNotFound();
        $this->get('/!/csrf.js')->assertNotFound();
    }
}
