<?php

namespace Tests\StaticCaching;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Globals\Variables;
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

    #[Test]
    public function specifying_all_as_invalidation_rule_will_just_flush_the_cache()
    {
        $cacher = Mockery::mock(Cacher::class)->shouldReceive('flush')->once()->getMock();
        $item = Mockery::mock(Entry::class);

        $invalidator = new Invalidator($cacher, 'all');

        $this->assertNull($invalidator->invalidate($item));
    }

    #[Test]
    public function assets_can_trigger_url_invalidation()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/page/one', '/page/two']);
        });

        $container = tap(Mockery::mock(AssetContainer::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('main');
        });

        $asset = tap(Mockery::mock(Asset::class), function ($m) use ($container) {
            $m->shouldReceive('container')->andReturn($container);
        });

        $invalidator = new Invalidator($cacher, [
            'assets' => [
                'main' => [
                    'urls' => [
                        '/page/one',
                        '/page/two',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($asset));
    }

    #[Test]
    public function collection_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrl')->with('/my/test/collection', 'http://test.com')->once();
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/blog/one', '/blog/two']);
        });

        $collection = tap(Mockery::mock(Collection::class), function ($m) {
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.com/my/test/collection');
            $m->shouldReceive('handle')->andReturn('blog');
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

        $this->assertNull($invalidator->invalidate($collection));
    }

    #[Test]
    public function collection_urls_can_be_invalidated_by_an_entry()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrl')->with('/my/test/entry', 'http://test.com')->once();
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/blog/one', '/blog/two']);
        });

        $entry = tap(Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('isRedirect')->andReturn(false);
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.com/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
            $m->shouldReceive('descendants')->andReturn(collect());
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

    #[Test]
    public function entry_urls_are_not_invalidated_by_an_entry_with_a_redirect()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrl')->never();
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/blog/one', '/blog/two']);
        });

        $entry = tap(Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('isRedirect')->andReturn(true);
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.com/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
            $m->shouldReceive('descendants')->andReturn(collect());
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

    #[Test]
    public function taxonomy_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrl')->with('/my/test/term', 'http://test.com')->once();
            $cacher->shouldReceive('invalidateUrl')->with('/my/collection/tags/term', 'http://test.com')->once();
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/tags/one', '/tags/two']);
        });

        $collection = Mockery::mock(Collection::class);

        $taxonomy = tap(Mockery::mock(Taxonomy::class), function ($m) use ($collection) {
            $m->shouldReceive('collections')->andReturn(collect([$collection]));
        });

        $term = tap(Mockery::mock(Term::class), function ($m) use ($taxonomy) {
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.com/my/test/term', 'http://test.com/my/collection/tags/term');
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

    #[Test]
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

    #[Test]
    public function globals_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/one', '/two']);
        });

        $set = tap(Mockery::mock(GlobalSet::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('social');
        });

        $variables = tap(Mockery::mock(Variables::class), function ($m) use ($set) {
            $m->shouldReceive('globalSet')->andReturn($set);
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

        $this->assertNull($invalidator->invalidate($variables));
    }

    #[Test]
    public function form_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->once()->with(['/one', '/two']);
        });

        $form = tap(Mockery::mock(Form::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('newsletter');
        });

        $invalidator = new Invalidator($cacher, [
            'forms' => [
                'newsletter' => [
                    'urls' => [
                        '/one',
                        '/two',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($form));
    }
}
