<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\File;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\Writer;
use Tests\TestCase;

class FileCacherTest extends TestCase
{
    #[Test]
    public function gets_cache_paths_when_multiple_paths_are_provided()
    {
        $cacher = $this->fileCacher([
            'path' => [
                'en' => 'test/path',
                'fr' => 'fr/test/path',
            ],
        ]);

        $this->assertEquals([
            'en' => 'test/path',
            'fr' => 'fr/test/path',
        ], $cacher->getCachePaths());
    }

    #[Test]
    public function gets_cache_paths_when_a_single_path_is_provided()
    {
        $cacher = $this->fileCacher([
            'locale' => 'en',
            'path' => 'test/path',
        ]);

        $this->assertEquals([
            'en' => 'test/path',
        ], $cacher->getCachePaths());
    }

    #[Test]
    public function gets_cache_path_when_multiple_paths_are_provided()
    {
        $cacher = $this->fileCacher([
            'locale' => 'en',
            'path' => [
                'en' => 'test/path',
                'fr' => 'fr/test/path',
            ],
        ]);

        $this->assertEquals('test/path', $cacher->getCachePath('en'));
        $this->assertEquals('fr/test/path', $cacher->getCachePath('fr'));
        $this->assertEquals('test/path', $cacher->getCachePath());
    }

    #[Test]
    public function gets_cache_path_when_a_single_path_is_provided()
    {
        $cacher = $this->fileCacher([
            'path' => 'test/path',
        ]);

        $this->assertEquals('test/path', $cacher->getCachePath());
    }

    #[Test]
    public function gets_file_path_from_url()
    {
        $cacher = $this->fileCacher([
            'path' => 'test/path',
            'max_filename_length' => 16,
        ]);

        $this->assertEquals(
            'test/path/foo/bar/baz/qux_c=d&a=b.html',
            $cacher->getFilePath('http://domain.com/foo/bar/baz/qux?c=d&a=b')
        );

        $this->assertEquals(
            'test/path/foo/bar_.html',
            $cacher->getFilePath('http://domain.com/foo/bar')
        );
    }

    #[Test]
    public function gets_file_path_from_url_and_hashes_long_query_strings()
    {
        $cacher = $this->fileCacher([
            'path' => 'test/path',
            'max_filename_length' => 30,
        ]);

        $query = 'baz=qux&one=two&three=four&five=six';

        $this->assertEquals(
            'test/path/foo/bar_lqs_'.md5($query).'.html',
            $cacher->getFilePath('http://domain.com/foo/bar?'.$query)
        );
    }

    #[Test]
    public function gets_file_path_from_url_and_ignores_query_strings()
    {
        $cacher = $this->fileCacher([
            'path' => 'test/path',
            'ignore_query_strings' => true,
        ]);

        $this->assertEquals(
            'test/path/foo/bar_.html',
            $cacher->getFilePath('http://domain.com/foo/bar?baz=qux&one=two')
        );

        $this->assertEquals(
            'test/path/foo/bar_.html',
            $cacher->getFilePath('http://domain.com/foo/bar')
        );
    }

    #[Test]
    public function gets_file_path_with_multiple_locations()
    {
        $cacher = $this->fileCacher([
            'locale' => 'en',
            'path' => [
                'en' => 'test/path',
                'fr' => 'fr/test/path',
            ],
        ]);

        $this->assertEquals(
            'test/path/foo/bar_.html',
            $cacher->getFilePath('http://domain.com/foo/bar')
        );

        $this->assertEquals(
            'fr/test/path/fr/foo/bar_.html',
            $cacher->getFilePath('http://domain.com/fr/foo/bar', 'fr')
        );
    }

    #[Test]
    public function flushing_the_cache_deletes_from_all_cache_locations()
    {
        $writer = \Mockery::spy(Writer::class);

        $cacher = $this->fileCacher([
            'locale' => 'en',
            'path' => [
                'en' => 'test/path',
                'fr' => 'fr/test/path',
            ],
        ], $writer);

        $cacher->flush();

        $writer->shouldHaveReceived('flush')->with('test/path');
        $writer->shouldHaveReceived('flush')->with('fr/test/path');
    }

    #[Test]
    public function invalidating_a_url_thats_not_cached_will_do_nothing()
    {
        $writer = \Mockery::spy(Writer::class);
        $cacher = $this->fileCacher();

        $cacher->invalidateUrl('/test');

        $writer->shouldNotHaveReceived('delete');
    }

    #[Test]
    public function invalidating_a_url_deletes_the_file_and_removes_the_url()
    {
        $writer = \Mockery::spy(Writer::class);
        $cache = app(Repository::class);
        $cacher = $this->fileCacher([], $writer, $cache, []);

        $cache->forever($this->cacheKey('http://example.com'), [
            'one' => '/one',
            'onemore' => '/onemore',
            'two' => '/two',
        ]);

        $cacher->invalidateUrl('/one', 'http://example.com');

        $writer->shouldHaveReceived('delete')->once();
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one'))->once();
        $this->assertEquals([
            'onemore' => '/onemore',
            'two' => '/two',
        ], $cacher->getUrls('http://example.com')->all());

        // TODO Check fallback to app url.
        // Config::set('app.url', 'http://example.com');
    }

    #[Test]
    public function invalidating_a_url_deletes_the_file_and_removes_the_url_for_query_string_versions_too()
    {
        $writer = \Mockery::spy(Writer::class);
        $cache = app(Repository::class);
        $cacher = $this->fileCacher([], $writer, $cache, []);

        $cache->forever($this->cacheKey('http://example.com'), [
            'one' => '/one',
            'oneqs' => '/one?foo=bar',
            'onemore' => '/onemore',
            'two' => '/two',
        ]);

        $cacher->invalidateUrl('/one', 'http://example.com');

        $writer->shouldHaveReceived('delete')->times(2);
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one'))->once();
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one?foo=bar'))->once();
        $this->assertEquals([
            'two' => '/two',
            'onemore' => '/onemore',
        ], $cacher->getUrls('http://example.com')->all());
    }

    #[Test]
    public function invalidating_a_url_deletes_the_file_and_removes_the_url_when_using_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]);

        $writer = \Mockery::spy(Writer::class);
        $cache = app(Repository::class);
        $cacher = $this->fileCacher([
            'path' => [
                'en' => 'test/path',
                'fr' => 'test/path/fr',
                'de' => 'test/path/de',
            ],
        ], $writer, $cache);

        $cache->forever($this->cacheKey('http://domain.com'), [
            'one' => '/one', 'two' => '/two',
            'un' => '/fr/un', 'deux' => '/fr/deux',
        ]);
        $cache->forever($this->cacheKey('http://domain.de'), [
            'one' => '/one', 'two' => '/two',
        ]);

        $cacher->invalidateUrl('/one', 'http://domain.com');
        $cacher->invalidateUrl('/fr/deux', 'http://domain.com');
        $cacher->invalidateUrl('/two', 'http://domain.de');

        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one', 'en'));
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/fr/deux', 'fr'));
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/two', 'de'));
        $this->assertEquals(['two' => '/two', 'un' => '/fr/un'], $cacher->getUrls('http://domain.com')->all());
        $this->assertEquals(['one' => '/one'], $cacher->getUrls('http://domain.de')->all());
    }

    #[Test]
    public function invalidating_a_url_deletes_the_file_and_removes_the_url_when_using_multisite_and_a_single_string_value_for_the_path()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]);

        $writer = \Mockery::spy(Writer::class);
        $cache = app(Repository::class);
        $cacher = $this->fileCacher(['path' => 'test/path'], $writer, $cache);

        $cache->forever($this->cacheKey('http://domain.com'), [
            'one' => '/one', 'two' => '/two',
            'un' => '/fr/un', 'deux' => '/fr/deux',
        ]);
        $cache->forever($this->cacheKey('http://domain.de'), [
            'one' => '/one', 'two' => '/two',
        ]);

        $cacher->invalidateUrl('/one', 'http://domain.com');
        $cacher->invalidateUrl('/fr/deux', 'http://domain.com');
        $cacher->invalidateUrl('/two', 'http://domain.de');

        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one', 'en'));
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/fr/deux', 'fr'));
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/two', 'de'));
        $this->assertEquals(['two' => '/two', 'un' => '/fr/un'], $cacher->getUrls('http://domain.com')->all());
        $this->assertEquals(['one' => '/one'], $cacher->getUrls('http://domain.de')->all());
    }

    #[Test]
    public function invalidating_a_url_deletes_the_file_even_if_it_is_not_in_application_cache()
    {
        $writer = \Mockery::spy(Writer::class);
        $cache = app(Repository::class);
        $cacher = $this->fileCacher([
            'path' => public_path('static'),
        ], $writer, $cache, []);

        File::put($cacher->getFilePath('/one'), '');
        File::put($cacher->getFilePath('/one?foo=bar'), '');
        File::put($cacher->getFilePath('/onemore'), '');
        File::put($cacher->getFilePath('/two'), '');

        $cacher->invalidateUrl('/one', 'http://example.com');

        File::delete($cacher->getFilePath('/one'));
        File::delete($cacher->getFilePath('/one?foo=bar'));
        File::delete($cacher->getFilePath('/onemore'));
        File::delete($cacher->getFilePath('/two'));

        $writer->shouldHaveReceived('delete')->times(2);
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one'))->once();
        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one?foo=bar'))->once();
    }

    #[Test]
    #[DataProvider('invalidateEventProvider')]
    public function invalidating_a_url_dispatches_event($domain, $expectedUrl)
    {
        Event::fake();

        $writer = \Mockery::spy(Writer::class);
        $cache = app(Repository::class);
        $cacher = $this->fileCacher(['base_url' => 'http://base.com'], $writer, $cache);

        // Put it in the container so that the event can resolve it.
        $this->instance(Cacher::class, $cacher);

        $cacher->invalidateUrl('/foo', $domain);

        Event::assertDispatched(UrlInvalidated::class, function ($event) use ($expectedUrl) {
            return $event->url === $expectedUrl;
        });
    }

    public static function invalidateEventProvider()
    {
        return [
            'no domain' => [null, 'http://base.com/foo'],
            'configured base domain' => ['http://base.com', 'http://base.com/foo'],
            'another domain' => ['http://another.com', 'http://another.com/foo'],
        ];
    }

    #[Test]
    #[DataProvider('currentUrlProvider')]
    public function it_gets_the_current_url(
        array $query,
        array $config,
        string $expectedUrl
    ) {
        $request = Request::create('http://example.com/test', 'GET', $query);

        $cacher = $this->fileCacher($config);

        $this->assertEquals($expectedUrl, $cacher->getUrl($request));
    }

    public static function currentUrlProvider()
    {
        return [
            'no query' => [
                [],
                [],
                'http://example.com/test',
            ],
            'with query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                [],
                'http://example.com/test?bravo=b&charlie=c&alfa=a',
            ],
            'with query, ignoring query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                ['ignore_query_strings' => true],
                'http://example.com/test',
            ],
            'with query, allowed query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                ['allowed_query_strings' => ['alfa', 'bravo']],
                'http://example.com/test?bravo=b&charlie=c&alfa=a', // allowed_query_strings has no effect
            ],
            'with query, disallowed query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                ['disallowed_query_strings' => ['charlie']],
                'http://example.com/test?bravo=b&charlie=c&alfa=a', // disallowed_query_strings has no effect

            ],
            'with query, allowed and disallowed' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                [
                    'allowed_query_strings' => ['alfa', 'bravo'],
                    'disallowed_query_strings' => ['bravo'],
                ],
                'http://example.com/test?bravo=b&charlie=c&alfa=a', // allowed_query_strings and disallowed_query_strings have no effect
            ],
        ];
    }

    private function cacheKey($domain)
    {
        return 'static-cache:'.md5($domain).'.urls';
    }

    private function fileCacher($config = [], $writer = null, $cache = null)
    {
        $writer = $writer ?: \Mockery::mock(Writer::class);

        $cache = $cache ?: app(Repository::class);

        // The locale would be set by the service provider.
        $config['locale'] = $config['locale'] ?? 'en';

        return new FileCacher($writer, $cache, $config);
    }
}
