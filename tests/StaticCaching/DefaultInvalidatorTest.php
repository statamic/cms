<?php

namespace Tests\StaticCaching;

use Mockery;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\DefaultInvalidator as Invalidator;

class DefaultInvalidatorTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function specifying_all_as_invalidation_rule_will_just_flush_the_cache()
    {
        $cacher = Mockery::mock(Cacher::class)->shouldReceive('flush')->once()->getMock();
        $item = Mockery::mock(Entry::class);

        $invalidator = new Invalidator($cacher, 'all');

        $this->assertNull($invalidator->invalidate($item));
    }

    /** @test */
    public function collection_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrl')->with('/my/test/entry')->once();
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/blog/one', '/blog/two']);
        });

        $entry = tap(Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($entry));
    }

    /** @test */
    public function taxonomy_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrl')->with('/my/test/term')->once();
            $cacher->shouldReceive('invalidateUrl')->with('/my/collection/tags/term')->once();
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/tags/one', '/tags/two']);
        });

        $collection = Mockery::mock(Collection::class);

        $taxonomy = tap(Mockery::mock(Taxonomy::class), function ($m) use ($collection) {
            $m->shouldReceive('collections')->andReturn(collect([$collection]));
        });

        $term = tap(Mockery::mock(Term::class), function ($m) use ($taxonomy) {
            $m->shouldReceive('url')->andReturn('/my/test/term', '/my/collection/tags/term');
            $m->shouldReceive('taxonomyHandle')->andReturn('tags');
            $m->shouldReceive('taxonomy')->andReturn($taxonomy);
            $m->shouldReceive('collection')->andReturn($m);
        });

        $invalidator = new Invalidator($cacher, [
            'taxonomies' => [
                'tags' => [
                    'urls' => [
                        '/tags/one',
                        '/tags/two',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($term));
    }

    /** @test */
    public function navigation_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/one', '/two']);
        });

        $nav = tap(Mockery::mock(Nav::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('links');
        });

        $invalidator = new Invalidator($cacher, [
            'navigation' => [
                'links' => [
                    'urls' => [
                        '/one',
                        '/two',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($nav));
    }

    /** @test */
    public function globals_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/one', '/two']);
        });

        $set = tap(Mockery::mock(GlobalSet::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('social');
        });

        $invalidator = new Invalidator($cacher, [
            'globals' => [
                'social' => [
                    'urls' => [
                        '/one',
                        '/two',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($set));
    }
}
