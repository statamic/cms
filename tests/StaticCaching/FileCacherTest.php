<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
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
    public function gets_cache_path()
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
            'test/path/foo/bar/baz/qux_a=b&c=d.html',
            $cacher->getFilePath('http://domain.com/foo/bar/baz/qux?a=b&c=d')
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
        $cacher = new FileCacher($writer, $cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);

        $cacher->invalidateUrl('/one');

        $writer->shouldHaveReceived('delete')->with($cacher->getFilePath('/one'));
        $this->assertEquals(['two' => '/two'], $cacher->getUrls()->all());
    }

    private function fileCacher($config = [], $writer = null)
    {
        $writer = $writer ?: \Mockery::mock(Writer::class);

        return new FileCacher($writer, app(Repository::class), $config);
    }
}
