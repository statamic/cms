<?php

namespace Tests\StaticCaching;

use Tests\TestCase;
use Statamic\Data\Pages\Page;
use Statamic\Data\Entries\Entry;
use Statamic\Data\Taxonomies\Term;
use Statamic\StaticCaching\Cacher;
use Statamic\Data\Content\Content;
use Statamic\StaticCaching\Invalidator;
use Statamic\Events\Stache\RepositoryItemInserted;

class InvalidatorTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        parent::tearDown();
    }

    /** @test */
    public function non_content_in_event_will_do_nothing()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher);
        $event = new RepositoryItemInserted('test', 'test', 'not a content object');

        $invalidator->handle($event);

        $cacher->shouldNotHaveReceived('invalidateUrl');
    }

    /** @test */
    public function specifying_all_as_invalidation_rule_will_just_flush_the_cache()
    {
        $cacher = $this->cacherSpy(['static_caching_invalidation' => 'all']);
        $invalidator = new Invalidator($cacher);
        $content = \Mockery::mock(Content::class);
        $event = new RepositoryItemInserted('test', 'test', $content);

        $invalidator->handle($event);

        $cacher->shouldHaveReceived('flush');
    }

    /** @test */
    public function collection_urls_get_invalidated()
    {
        $cacher = $this->cacherSpy([
            'static_caching_invalidation' => [
                'collections' => [
                    'blog' => [
                        'urls' => ['/url/one', '/url/two']
                    ]
                ]
            ]
        ]);
        $invalidator = new Invalidator($cacher);
        $entry = tap(\Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/entry');
            $m->shouldReceive('contentType')->andReturn('entry');
            $m->shouldReceive('collectionName')->andReturn('blog');
        });
        $event = new RepositoryItemInserted('test', 'test', $entry);

        $invalidator->handle($event);

        $cacher->shouldHaveReceived('invalidateUrl')->with('/my/test/entry');
        $cacher->shouldHaveReceived('invalidateUrls')->with([
            '/url/one', '/url/two'
        ]);
    }

    /** @test */
    public function taxonomy_urls_get_invalidated()
    {
        $cacher = $this->cacherSpy([
            'static_caching_invalidation' => [
                'taxonomies' => [
                    'tags' => [
                        'urls' => ['/url/one', '/url/two']
                    ]
                ]
            ]
        ]);
        $invalidator = new Invalidator($cacher);
        $term = tap(\Mockery::mock(Term::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/term');
            $m->shouldReceive('contentType')->andReturn('term');
            $m->shouldReceive('taxonomyName')->andReturn('tags');
        });
        $event = new RepositoryItemInserted('test', 'test', $term);

        $invalidator->handle($event);

        $cacher->shouldHaveReceived('invalidateUrl')->with('/my/test/term');
        $cacher->shouldHaveReceived('invalidateUrls')->with([
            '/url/one', '/url/two'
        ]);
    }

    /** @test */
    public function page_urls_get_invalidated()
    {
        $this->markTestSkipped(); // until pages return!

        $cacher = $this->cacherSpy([
            'static_caching_invalidation' => [
                'pages' => [
                    '/my/test/page' => [
                        'urls' => ['/url/one', '/url/two']
                    ]
                ]
            ]
        ]);
        $invalidator = new Invalidator($cacher);
        $page = tap(\Mockery::mock(Page::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/page');
            $m->shouldReceive('contentType')->andReturn('page');
        });
        $event = new RepositoryItemInserted('test', 'test', $page);

        $invalidator->handle($event);

        $cacher->shouldHaveReceived('invalidateUrl')->with('/my/test/page');
        $cacher->shouldHaveReceived('invalidateUrls')->with([
            '/url/one', '/url/two'
        ]);
    }

    private function cacherSpy($settings)
    {
        $m = \Mockery::spy(Cacher::class);

        $m->shouldReceive('config')->with('invalidation')->andReturn($settings['static_caching_invalidation']);

        return $m;
    }
}
