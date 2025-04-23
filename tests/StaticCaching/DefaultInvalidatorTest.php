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
use Statamic\Facades\Site;
use Statamic\Globals\Variables;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\DefaultInvalidator as Invalidator;
use Statamic\Structures\CollectionTree;
use Statamic\Structures\NavTree;
use Statamic\Structures\Structure;
use Statamic\Taxonomies\LocalizedTerm;
use Tests\TestCase;

class DefaultInvalidatorTest extends TestCase
{
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
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/page/three',
                'http://localhost/page/one',
                'http://localhost/page/two',
            ])->once();
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
                        'http://localhost/page/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($asset));
    }

    #[Test]
    public function assets_can_trigger_url_invalidation_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/page/three',
                'http://test.com/page/one',
                'http://test.com/page/two',
                'http://test.fr/page/one',
                'http://test.fr/page/two',
            ])->once();
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
                        'http://test.com/page/three',
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
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/my/test/collection',
                'http://localhost/blog/three',
                'http://localhost/blog/one',
                'http://localhost/blog/two',
            ])->once();
        });

        $collection = tap(Mockery::mock(Collection::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('blog');
            $m->shouldReceive('sites')->andReturn(collect(['en']));
            $m->shouldReceive('absoluteUrl')->with('en')->andReturn('http://localhost/my/test/collection');
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                        'http://localhost/blog/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($collection));
    }

    #[Test]
    public function collection_urls_can_be_invalidated_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
            'de' => ['url' => 'http://test.de', 'locale' => 'de_DE'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/my/test/collection',
                'http://test.fr/my/test/collection',
                'http://test.com/blog/three',
                'http://test.com/blog/one',
                'http://test.com/blog/two',
                'http://test.fr/blog/one',
                'http://test.fr/blog/two',
            ])->once();
        });

        $collection = tap(Mockery::mock(Collection::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('blog');
            $m->shouldReceive('sites')->andReturn(collect(['en', 'fr']));

            $m->shouldReceive('absoluteUrl')->with('en')->andReturn('http://test.com/my/test/collection')->once();
            $m->shouldReceive('absoluteUrl')->with('fr')->andReturn('http://test.fr/my/test/collection')->once();
            $m->shouldReceive('absoluteUrl')->with('de')->never();
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                        'http://test.com/blog/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($collection));
    }

    #[Test]
    public function collection_urls_can_be_invalidated_by_a_tree()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/blog/three',
                'http://localhost/blog/one',
                'http://localhost/blog/two',
            ])->once();
        });

        $collection = tap(Mockery::mock(Collection::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('blog');
            $m->shouldReceive('sites')->andReturn(collect(['en']));
        });

        $structure = tap(Mockery::mock(Structure::class), function ($m) use ($collection) {
            $m->shouldReceive('collection')->andReturn($collection);
        });

        $tree = tap(Mockery::mock(CollectionTree::class), function ($m) use ($collection, $structure) {
            $m->shouldReceive('structure')->andReturn($structure);
            $m->shouldReceive('collection')->andReturn($collection);
            $m->shouldReceive('site')->andReturn(Site::default());
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                        'http://localhost/blog/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($tree));
    }

    #[Test]
    public function collection_urls_can_be_invalidated_by_a_tree_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/blog/three',
                'http://test.fr/blog/one',
                'http://test.fr/blog/two',
            ])->once();
        });

        $collection = tap(Mockery::mock(Collection::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('blog');
            $m->shouldReceive('sites')->andReturn(collect(['en']));
        });

        $structure = tap(Mockery::mock(Structure::class), function ($m) use ($collection) {
            $m->shouldReceive('collection')->andReturn($collection);
        });

        $tree = tap(Mockery::mock(CollectionTree::class), function ($m) use ($collection, $structure) {
            $m->shouldReceive('structure')->andReturn($structure);
            $m->shouldReceive('collection')->andReturn($collection);
            $m->shouldReceive('site')->andReturn(Site::get('fr'));
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                        'http://localhost/blog/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($tree));
    }

    #[Test]
    public function collection_urls_can_be_invalidated_by_an_entry()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/my/test/entry',
                'http://localhost/blog/three',
                'http://localhost/blog/one',
                'http://localhost/blog/two',
            ])->once();
        });

        $entry = tap(Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('isRedirect')->andReturn(false);
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.com/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
            $m->shouldReceive('descendants')->andReturn(collect());
            $m->shouldReceive('site')->andReturn(Site::default());
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                        'http://localhost/blog/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($entry));
    }

    #[Test]
    public function collection_urls_can_be_invalidated_by_an_entry_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.fr/my/test/entry',
                'http://test.com/blog/three',
                'http://test.fr/blog/one',
                'http://test.fr/blog/two',
            ])->once();
        });

        $entry = tap(Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('isRedirect')->andReturn(false);
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.fr/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
            $m->shouldReceive('descendants')->andReturn(collect());
            $m->shouldReceive('site')->andReturn(Site::get('fr'));
        });

        $invalidator = new Invalidator($cacher, [
            'collections' => [
                'blog' => [
                    'urls' => [
                        '/blog/one',
                        '/blog/two',
                        'http://test.com/blog/three',
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
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/blog/one',
                'http://localhost/blog/two',
            ])->once();
        });

        $entry = tap(Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('isRedirect')->andReturn(true);
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.com/my/test/entry');
            $m->shouldReceive('collectionHandle')->andReturn('blog');
            $m->shouldReceive('descendants')->andReturn(collect());
            $m->shouldReceive('site')->andReturn(Site::default());
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
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/my/test/term',
                'http://localhost/my/collection/tags/term',
                'http://localhost/tags/three',
                'http://localhost/tags/one',
                'http://localhost/tags/two',
            ])->once();
        });

        $collection = Mockery::mock(Collection::class);

        $taxonomy = tap(Mockery::mock(Taxonomy::class), function ($m) use ($collection) {
            $m->shouldReceive('collections')->andReturn(collect([$collection]));
        });

        $term = Mockery::mock(Term::class);

        $localized = tap(Mockery::mock(LocalizedTerm::class), function ($m) use ($term, $taxonomy) {
            $m->shouldReceive('term')->andReturn($term);
            $m->shouldReceive('taxonomyHandle')->andReturn('tags');
            $m->shouldReceive('taxonomy')->andReturn($taxonomy);
            $m->shouldReceive('collection')->andReturn($m);
            $m->shouldReceive('site')->andReturn(Site::default());
            $m->shouldReceive('absoluteUrl')->andReturn('http://localhost/my/test/term', 'http://localhost/my/collection/tags/term');
        });

        $invalidator = new Invalidator($cacher, [
            'taxonomies' => [
                'tags' => [
                    'urls' => [
                        '/tags/one',
                        '/tags/two',
                        'http://localhost/tags/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($localized));
    }

    #[Test]
    public function taxonomy_urls_can_be_invalidated_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.fr/my/test/term',
                'http://test.fr/my/collection/tags/term',
                'http://test.com/tags/three',
                'http://test.fr/tags/one',
                'http://test.fr/tags/two',
            ])->once();
        });

        $collection = Mockery::mock(Collection::class);

        $taxonomy = tap(Mockery::mock(Taxonomy::class), function ($m) use ($collection) {
            $m->shouldReceive('collections')->andReturn(collect([$collection]));
        });

        $term = Mockery::mock(Term::class);

        $localized = tap(Mockery::mock(LocalizedTerm::class), function ($m) use ($term, $taxonomy) {
            $m->shouldReceive('term')->andReturn($term);
            $m->shouldReceive('taxonomyHandle')->andReturn('tags');
            $m->shouldReceive('taxonomy')->andReturn($taxonomy);
            $m->shouldReceive('collection')->andReturn($m);
            $m->shouldReceive('site')->andReturn(Site::get('fr'));
            $m->shouldReceive('absoluteUrl')->andReturn('http://test.fr/my/test/term', 'http://test.fr/my/collection/tags/term');
        });

        $invalidator = new Invalidator($cacher, [
            'taxonomies' => [
                'tags' => [
                    'urls' => [
                        '/tags/one',
                        '/tags/two',
                        'http://test.com/tags/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($localized));
    }

    #[Test]
    public function navigation_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/three',
                'http://localhost/one',
                'http://localhost/two',
            ])->once();
        });

        $nav = tap(Mockery::mock(Nav::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('links');
            $m->shouldReceive('sites')->andReturn(collect(['en']));
        });

        $invalidator = new Invalidator($cacher, [
            'navigation' => [
                'links' => [
                    'urls' => [
                        '/one',
                        '/two',
                        'http://localhost/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($nav));
    }

    #[Test]
    public function navigation_urls_can_be_invalidated_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
            'de' => ['url' => 'http://test.de', 'locale' => 'de_DE'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/three',
                'http://test.com/one',
                'http://test.com/two',
                'http://test.fr/one',
                'http://test.fr/two',
            ])->once();
        });

        $nav = tap(Mockery::mock(Nav::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('links');
            $m->shouldReceive('sites')->andReturn(collect(['en', 'fr']));
        });

        $invalidator = new Invalidator($cacher, [
            'navigation' => [
                'links' => [
                    'urls' => [
                        '/one',
                        '/two',
                        'http://test.com/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($nav));
    }

    #[Test]
    public function navigation_urls_can_be_invalidated_by_a_tree()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/three',
                'http://localhost/one',
                'http://localhost/two',
            ])->once();
        });

        $nav = tap(Mockery::mock(Nav::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('links');
        });

        $tree = tap(Mockery::mock(NavTree::class), function ($m) use ($nav) {
            $m->shouldReceive('structure')->andReturn($nav);
            $m->shouldReceive('site')->andReturn(Site::default());
        });

        $invalidator = new Invalidator($cacher, [
            'navigation' => [
                'links' => [
                    'urls' => [
                        '/one',
                        '/two',
                        'http://localhost/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($tree));
    }

    #[Test]
    public function navigation_urls_can_be_invalidated_by_a_tree_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/three',
                'http://test.fr/one',
                'http://test.fr/two',
            ])->once();
        });

        $nav = tap(Mockery::mock(Nav::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('links');
        });

        $tree = tap(Mockery::mock(NavTree::class), function ($m) use ($nav) {
            $m->shouldReceive('structure')->andReturn($nav);
            $m->shouldReceive('site')->andReturn(Site::get('fr'));
        });

        $invalidator = new Invalidator($cacher, [
            'navigation' => [
                'links' => [
                    'urls' => [
                        '/one',
                        '/two',
                        'http://test.com/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($tree));
    }

    #[Test]
    public function globals_urls_can_be_invalidated()
    {
        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/three',
                'http://localhost/one',
                'http://localhost/two',
            ])->once();
        });

        $set = tap(Mockery::mock(GlobalSet::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('social');
        });

        $variables = tap(Mockery::mock(Variables::class), function ($m) use ($set) {
            $m->shouldReceive('globalSet')->andReturn($set);
            $m->shouldReceive('site')->andReturn(Site::default());
        });

        $invalidator = new Invalidator($cacher, [
            'globals' => [
                'social' => [
                    'urls' => [
                        '/one',
                        '/two',
                        'http://localhost/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($variables));
    }

    #[Test]
    public function globals_urls_can_be_invalidated_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/three',
                'http://test.fr/one',
                'http://test.fr/two',
            ])->once();
        });

        $set = tap(Mockery::mock(GlobalSet::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('social');
        });

        $variables = tap(Mockery::mock(Variables::class), function ($m) use ($set) {
            $m->shouldReceive('globalSet')->andReturn($set);
            $m->shouldReceive('site')->andReturn(Site::get('fr'));
        });

        $invalidator = new Invalidator($cacher, [
            'globals' => [
                'social' => [
                    'urls' => [
                        '/one',
                        '/two',
                        'http://test.com/three',
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
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://localhost/three',
                'http://localhost/one',
                'http://localhost/two',
            ])->once();
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
                        'http://localhost/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($form));
    }

    #[Test]
    public function form_urls_can_be_invalidated_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://test.com', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://test.fr', 'locale' => 'fr_FR'],
        ]);

        $cacher = tap(Mockery::mock(Cacher::class), function ($cacher) {
            $cacher->shouldReceive('invalidateUrls')->with([
                'http://test.com/three',
                'http://test.com/one',
                'http://test.com/two',
                'http://test.fr/one',
                'http://test.fr/two',
            ])->once();
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
                        'http://test.com/three',
                    ],
                ],
            ],
        ]);

        $this->assertNull($invalidator->invalidate($form));
    }
}
