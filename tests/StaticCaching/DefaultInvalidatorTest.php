<?php

namespace Tests\StaticCaching;

use Statamic\Contracts\Data\Content\Content;
use Statamic\Contracts\Entries\Entry;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\DefaultInvalidator as Invalidator;
use Statamic\Contracts\Taxonomies\Term;

class DefaultInvalidatorTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        parent::tearDown();
    }

    /** @test */
    public function specifying_all_as_invalidation_rule_will_just_flush_the_cache()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher, 'all');
        $content = \Mockery::mock(Content::class);

        $invalidator->invalidate($content);

        $cacher->shouldHaveReceived('flush');
    }

    /** @test */
    public function the_entrys_url_gets_invalidated()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher);

        $entry = tap(\Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
        });

        $invalidator->invalidate($entry);

        $cacher->shouldNotHaveReceived('flush');
        $cacher->shouldHaveReceived('invalidateUrl')->with('/my/test/entry');
    }

    /** @test */
    public function collection_urls_can_be_invalidated()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two'
                    ]
                ],
            ]
        ]);

        $entry = tap(\Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
        });

        $invalidator->invalidate($entry);

        $cacher->shouldNotHaveReceived('flush');
        $cacher->shouldHaveReceived('invalidateUrls')->once()->with([
            '/blog/one',
            '/blog/two'
        ]);
    }

    /** @test */
    public function taxonomy_urls_can_be_invalidated()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher, [
            'taxonomies' => [
                'tags' => [
                    'urls' => [
                        '/tags/one',
                        '/tags/two'
                    ]
                ],
            ]
        ]);

        $entry = tap(\Mockery::mock(Term::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/term');
            $m->shouldReceive('taxonomyHandle')->andReturn('tags');
        });

        $invalidator->invalidate($entry);

        $cacher->shouldNotHaveReceived('flush');
        $cacher->shouldHaveReceived('invalidateUrls')->once()->with([
            '/tags/one',
            '/tags/two'
        ]);
    }
}
