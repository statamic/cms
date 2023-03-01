<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\Writer;
use Tests\TestCase;

class FileCacherTest extends TestCase
{
    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function gets_cache_path_when_a_single_path_is_provided()
    {
        $cacher = $this->fileCacher([
            'path' => 'test/path',
        ]);

        $this->assertEquals('test/path', $cacher->getCachePath());
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function invalidating_a_url_thats_not_cached_will_do_nothing()
    {
        $writer = \Mockery::spy(Writer::class);
        $cacher = $this->fileCacher();

        $cacher->invalidateUrl('/test');

        $writer->shouldNotHaveReceived('delete');
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function invalidating_a_url_deletes_the_file_and_removes_the_url_when_using_multisite()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]]);

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

    private function cacheKey($domain)
    {
        return 'static-cache:'.md5($domain).'.urls';
    }

    private function fileCacher($config = [], $writer = null, $cache = null)
    {
        $writer = $writer ?: \Mockery::mock(Writer::class);

        $cache = $cache ?: app(Repository::class);

        return new FileCacher($writer, $cache, $config);
    }
}
