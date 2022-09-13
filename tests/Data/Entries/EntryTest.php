<?php

namespace Tests\Data\Entries;

use BadMethodCallException;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Stache\Repositories\CollectionTreeRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Mockery;
use ReflectionClass;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\AugmentedCollection;
use Statamic\Entries\AugmentedEntry;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Events\EntryCreated;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Facades;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Sites\Site;
use Statamic\Structures\CollectionStructure;
use Statamic\Structures\CollectionTree;
use Statamic\Structures\Page;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_sets_and_gets_the_locale()
    {
        Facades\Site::setConfig(['sites' => [
            'foo' => [],
            'bar' => [],
        ]]);

        $entry = new Entry;
        $this->assertEquals('foo', $entry->locale()); // defaults to the default site.

        $return = $entry->locale('bar');

        $this->assertEquals($entry, $return);
        $this->assertEquals('bar', $entry->locale());
    }

    /** @test */
    public function it_gets_the_site()
    {
        config(['statamic.sites.sites' => [
            'en' => ['locale' => 'en_US'],
        ]]);

        $entry = (new Entry)->locale('en');

        $site = $entry->site();
        $this->assertInstanceOf(Site::class, $site);
        $this->assertEquals('en_US', $site->locale());
    }

    /** @test */
    public function it_sets_and_gets_the_slug()
    {
        $entry = new Entry;
        $this->assertNull($entry->slug());

        $return = $entry->slug('foo');

        $this->assertEquals($entry, $return);
        $this->assertEquals('foo', $entry->slug());
    }

    /** @test */
    public function the_slug_gets_slugified()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['locale' => 'en_US', 'url' => '/'],
            'da' => ['locale' => 'da_DK', 'url' => '/da/'],
        ]]);

        $entry = new Entry;
        $entry->slug('foo bar æøå');
        $this->assertEquals('foo-bar-aeoa', $entry->slug());

        $entry->locale('da');
        $this->assertEquals('foo-bar-aeoeaa', $entry->slug()); // danish replaces æøå with aeoeaa
    }

    /** @test */
    public function explicitly_setting_slug_to_null_will_return_null()
    {
        $entry = new Entry;
        $entry->slug(null);
        $this->assertNull($entry->slug());
    }

    /** @test */
    public function if_the_slug_is_a_function_it_will_resolve_it()
    {
        $entry = new Entry;
        $entry->set('title', 'Foo Bar');
        $entry->slug(function ($entry) {
            return $entry->get('title');
        });
        $this->assertEquals('foo-bar', $entry->slug());
    }

    /** @test */
    public function it_sets_gets_and_removes_data_values()
    {
        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->collection('test');
        $this->assertNull($entry->get('foo'));

        $return = $entry->set('foo', 'bar');

        $this->assertEquals($entry, $return);
        $this->assertTrue($entry->has('foo'));
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('bar', $entry->value('foo'));
        $this->assertEquals('fallback', $entry->get('unknown', 'fallback'));

        $return = $entry->remove('foo');

        $this->assertEquals($entry, $return);
        $this->assertFalse($entry->has('foo'));
    }

    /** @test */
    public function it_sets_data_values_using_magic_properties()
    {
        $entry = new Entry;
        $this->assertNull($entry->get('foo'));

        $entry->foo = 'bar';

        $this->assertTrue($entry->has('foo'));
        $this->assertEquals('bar', $entry->get('foo'));
    }

    /** @test */
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return $value.' (augmented)';
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['charlie' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['blog' => $blueprint]));
        Collection::make('blog')->save();

        $entry = (new Entry)->collection('blog')->id('123');
        $entry->set('alfa', 'bravo');
        $entry->set('charlie', 'delta');

        $this->assertEquals('123', $entry->id);
        $this->assertEquals('123', $entry['id']);
        $this->assertEquals('bravo', $entry->alfa);
        $this->assertEquals('bravo', $entry['alfa']);
        $this->assertEquals('delta (augmented)', $entry->charlie);
        $this->assertEquals('delta (augmented)', $entry['charlie']);
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     **/
    public function it_has_magic_property_and_methods_for_fields_that_augment_to_query_builders($builder)
    {
        $builder->shouldReceive('get')->times(2)->andReturn('query builder results');
        app()->instance('mocked-builder', $builder);

        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return app('mocked-builder');
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['foo' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['blog' => $blueprint]));
        Collection::make('blog')->save();

        $entry = (new Entry)->collection('blog');
        $entry->set('foo', 'delta');

        $this->assertEquals('query builder results', $entry->foo);
        $this->assertEquals('query builder results', $entry['foo']);
        $this->assertSame($builder, $entry->foo());
    }

    public function queryBuilderProvider()
    {
        return [
            'statamic' => [Mockery::mock(\Statamic\Query\Builder::class)],
            'database' => [Mockery::mock(\Illuminate\Database\Query\Builder::class)],
            'eloquent' => [Mockery::mock(\Illuminate\Database\Eloquent\Builder::class)],
        ];
    }

    /** @test */
    public function calling_unknown_method_throws_exception()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Statamic\Entries\Entry::thisFieldDoesntExist()');

        Collection::make('blog')->save();
        (new Entry)->collection('blog')->thisFieldDoesntExist();
    }

    /** @test */
    public function it_gets_and_sets_all_data()
    {
        $entry = new Entry;
        $this->assertEquals([], $entry->data()->all());

        $return = $entry->data(['foo' => 'bar']);

        $this->assertEquals($entry, $return);
        $this->assertEquals(['foo' => 'bar'], $entry->data()->all());
    }

    /** @test */
    public function it_merges_in_additional_data()
    {
        $entry = (new Entry)->data([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $return = $entry->merge([
            'bar' => 'merged bar',
            'qux' => 'merged qux',
        ]);

        $this->assertEquals($entry, $return);
        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'merged bar',
            'baz' => 'qux',
            'qux' => 'merged qux',
        ], $entry->data()->all());
    }

    /** @test */
    public function values_fall_back_to_the_origin_then_the_collection()
    {
        $collection = tap(Collection::make('test'))->save();
        $origin = (new Entry)->collection('test');
        $entry = (new Entry)->origin($origin)->collection('test');

        $this->assertNull($entry->value('test'));

        $collection->cascade(['test' => 'from collection']);
        $this->assertEquals('from collection', $entry->value('test'));

        $origin->set('test', 'from origin');
        $this->assertEquals('from origin', $entry->value('test'));
    }

    /** @test */
    public function it_gets_values_from_origin_and_collection()
    {
        tap(Collection::make('test')->cascade([
            'one' => 'one in collection',
            'two' => 'two in collection',
            'three' => 'three in collection',
        ]))->save();

        $origin = (new Entry)->collection('test')->data([
            'two' => 'two in origin',
            'three' => 'three in origin',
        ]);

        $entry = (new Entry)->origin($origin)->collection('test')->data([
            'three' => 'three in entry',
        ]);

        $this->assertEquals([
            'one' => 'one in collection',
            'two' => 'two in origin',
            'three' => 'three in entry',
        ], $entry->values()->all());

        $this->assertEquals('one in collection', $entry->value('one'));
        $this->assertEquals('two in origin', $entry->value('two'));
        $this->assertEquals('three in entry', $entry->value('three'));
    }

    /** @test */
    public function if_the_value_is_explicitly_set_to_null_then_it_should_not_fall_back()
    {
        tap(Collection::make('test')->cascade([
            'one' => 'one in collection',
            'two' => 'two in collection',
            'three' => 'three in collection',
            'four' => 'four in collection',
        ]))->save();

        $origin = (new Entry)->collection('test')->data([
            'two' => null,
            'three' => 'three in origin',
            'four' => 'four in origin',
        ]);

        $entry = (new Entry)->origin($origin)->collection('test')->data([
            'three' => null,
            'four' => 'four in entry',
        ]);

        $this->assertEquals([
            'one' => 'one in collection', // falls all the way back
            'two' => null, // falls back from entry, stops at origin
            'three' => null, // stops at entry
            'four' => 'four in entry', // set in entry
        ], $entry->values()->all());

        $this->assertEquals('one in collection', $entry->value('one'));
        $this->assertEquals(null, $entry->value('two'));
        $this->assertEquals(null, $entry->value('three'));
        $this->assertEquals('four in entry', $entry->value('four'));
    }

    /** @test */
    public function it_gets_the_url_from_the_collection()
    {
        config(['statamic.amp.enabled' => true]);

        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr_FR'],
            'de' => ['url' => 'http://domain.de/', 'locale' => 'de_DE'],
        ]]);

        $collection = (new Collection)->sites(['en', 'fr', 'de'])->handle('blog')->ampable(true)->routes([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
            'de' => 'das-blog/{slug}',
        ]);
        $collection->save();
        $entryEn = (new Entry)->collection($collection)->locale('en')->slug('foo');
        $entryFr = (new Entry)->collection($collection)->locale('fr')->slug('le-foo');
        $entryDe = (new Entry)->collection($collection)->locale('de')->slug('das-foo');
        $redirectEntry = (new Entry)->collection($collection)->locale('en')->slug('redirected')->set('redirect', 'http://example.com/page');
        $redirect404Entry = (new Entry)->collection($collection)->locale('en')->slug('redirect-404')->set('redirect', '404');

        $this->assertEquals('/blog/foo', $entryEn->uri());
        $this->assertEquals('/blog/foo', $entryEn->url());
        $this->assertEquals('/blog/foo', $entryEn->urlWithoutRedirect());
        $this->assertEquals('http://domain.com/blog/foo', $entryEn->absoluteUrl());
        $this->assertEquals('http://domain.com/blog/foo', $entryEn->absoluteUrlWithoutRedirect());
        $this->assertEquals('http://domain.com/amp/blog/foo', $entryEn->ampUrl());
        $this->assertNull($entryEn->redirectUrl());

        $this->assertEquals('/le-blog/le-foo', $entryFr->uri());
        $this->assertEquals('/fr/le-blog/le-foo', $entryFr->url());
        $this->assertEquals('/fr/le-blog/le-foo', $entryFr->urlWithoutRedirect());
        $this->assertEquals('http://domain.com/fr/le-blog/le-foo', $entryFr->absoluteUrl());
        $this->assertEquals('http://domain.com/fr/le-blog/le-foo', $entryFr->absoluteUrlWithoutRedirect());
        $this->assertEquals('http://domain.com/fr/amp/le-blog/le-foo', $entryFr->ampUrl());
        $this->assertNull($entryFr->redirectUrl());

        $this->assertEquals('/das-blog/das-foo', $entryDe->uri());
        $this->assertEquals('/das-blog/das-foo', $entryDe->url());
        $this->assertEquals('/das-blog/das-foo', $entryDe->urlWithoutRedirect());
        $this->assertEquals('http://domain.de/das-blog/das-foo', $entryDe->absoluteUrl());
        $this->assertEquals('http://domain.de/das-blog/das-foo', $entryDe->absoluteUrlWithoutRedirect());
        $this->assertEquals('http://domain.de/amp/das-blog/das-foo', $entryDe->ampUrl());
        $this->assertNull($entryDe->redirectUrl());

        $this->assertEquals('/blog/redirected', $redirectEntry->uri());
        $this->assertEquals('http://example.com/page', $redirectEntry->url());
        $this->assertEquals('/blog/redirected', $redirectEntry->urlWithoutRedirect());
        $this->assertEquals('http://example.com/page', $redirectEntry->absoluteUrl());
        $this->assertEquals('http://domain.com/blog/redirected', $redirectEntry->absoluteUrlWithoutRedirect());
        $this->assertNull($redirectEntry->ampUrl());
        $this->assertEquals('http://example.com/page', $redirectEntry->redirectUrl());

        $this->assertEquals('/blog/redirect-404', $redirect404Entry->uri());
        $this->assertEquals('/blog/redirect-404', $redirect404Entry->url());
        $this->assertEquals('/blog/redirect-404', $redirect404Entry->urlWithoutRedirect());
        $this->assertEquals('http://domain.com/blog/redirect-404', $redirect404Entry->absoluteUrl());
        $this->assertEquals('http://domain.com/blog/redirect-404', $redirect404Entry->absoluteUrlWithoutRedirect());
        $this->assertEquals('http://domain.com/amp/blog/redirect-404', $redirect404Entry->ampUrl());
        $this->assertEquals(404, $redirect404Entry->redirectUrl());
    }

    /** @test */
    public function it_gets_the_uri_from_the_structure()
    {
        $structure = $this->partialMock(CollectionStructure::class);
        $collection = tap((new Collection)->handle('test')->structure($structure)->routes('{parent_uri}/{slug}'))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug('foo');
        $structure->shouldReceive('entryUri')->with($entry)->once()->andReturn('/structured-uri');

        $this->assertEquals('/structured-uri', $entry->uri());
    }

    /** @test */
    public function entries_in_a_collection_without_a_route_dont_have_a_uri()
    {
        $collection = tap((new Collection)->handle('test'))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug('foo');

        $this->assertNull($entry->uri());
        $this->assertNull($entry->url());
    }

    /** @test */
    public function a_localized_entry_without_a_route_for_that_site_doesnt_have_a_uri()
    {
        $collection = tap((new Collection)->handle('test')->routes([
            'en' => '/test/{slug}',
        ]))->save();
        $entry = (new Entry)->collection($collection)->locale('fr')->slug('foo');

        $this->assertNull($entry->uri());
        $this->assertNull($entry->url());
    }

    /** @test */
    public function entries_in_a_structured_collection_without_a_route_dont_have_a_uri()
    {
        $structure = $this->partialMock(CollectionStructure::class);
        $collection = tap((new Collection)->handle('test')->structure($structure))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug('foo');

        $this->assertNull($entry->uri());
        $this->assertNull($entry->url());
    }

    /** @test */
    public function a_localized_entry_in_a_structured_collection_without_a_route_for_that_site_doesnt_have_a_uri()
    {
        $structure = $this->partialMock(CollectionStructure::class);
        $collection = tap((new Collection)->handle('test')->structure($structure)->routes([
            'en' => '/test/{slug}',
        ]))->save();
        $entry = (new Entry)->collection($collection)->locale('fr')->slug('foo');

        $this->assertNull($entry->uri());
        $this->assertNull($entry->url());
    }

    /** @test */
    public function it_gets_urls_for_first_child_redirects()
    {
        \Event::fake(); // Don't invalidate static cache etc when saving entries.

        $collection = tap((new Collection)->handle('pages')->routes('{parent_uri}/{slug}'))->save();

        $parent = tap((new Entry)->id('1')->locale('en')->collection($collection)->slug('parent')->set('redirect', '@child'))->save();
        $child = tap((new Entry)->id('2')->locale('en')->collection($collection)->slug('child'))->save();
        $noChildren = tap((new Entry)->id('3')->locale('en')->collection($collection)->slug('nochildren')->set('redirect', '@child'))->save();

        $collection->structureContents([
            'expects_root' => false, // irrelevant. just can't pass an empty array at the moment.
        ])->save();
        $collection->structure()->in('en')->tree(
            [
                [
                    'entry' => '1',
                    'children' => [
                        ['entry' => '2'],
                    ],
                ],
                [
                    'entry' => '3',
                ],
            ]
        )->save();

        $this->assertEquals('/parent', $parent->uri());
        $this->assertEquals('/parent/child', $parent->url());
        $this->assertEquals('/parent', $parent->urlWithoutRedirect());
        $this->assertEquals('/parent/child', $parent->redirectUrl());

        $this->assertEquals('/parent/child', $child->uri());
        $this->assertEquals('/parent/child', $child->url());
        $this->assertEquals('/parent/child', $child->urlWithoutRedirect());
        $this->assertNull($child->redirectUrl());

        $this->assertEquals('/nochildren', $noChildren->uri());
        $this->assertEquals('/nochildren', $noChildren->url());
        $this->assertEquals('/nochildren', $noChildren->urlWithoutRedirect());
        $this->assertEquals(404, $noChildren->redirectUrl());
    }

    /** @test */
    public function it_gets_and_sets_supplemental_data()
    {
        $entry = new Entry;
        $this->assertEquals([], $entry->supplements()->all());

        $return = $entry->setSupplement('foo', 'bar')->setSupplement('baz', null);

        $this->assertEquals($entry, $return);
        $this->assertEquals('bar', $entry->getSupplement('foo'));
        $this->assertNull($entry->getSupplement('bar'));
        $this->assertNull($entry->getSupplement('baz'));
        $this->assertTrue($entry->hasSupplement('foo'));
        $this->assertFalse($entry->hasSupplement('bar'));
        $this->assertTrue($entry->hasSupplement('baz'));
        $this->assertEquals(['foo' => 'bar', 'baz' => null], $entry->supplements()->all());
    }

    /** @test */
    public function it_compiles_augmented_array_data()
    {
        // The values of the augmented array are tested in AugmentedEntryTest

        $entry = (new Entry)
            ->collection(Collection::make('blog')->routes('blog/{slug}')->save());

        $this->assertInstanceOf(Augmentable::class, $entry);
        $this->assertInstanceOf(AugmentedEntry::class, $entry->newAugmentedInstance());
        $this->assertInstanceOf(AugmentedCollection::class, $entry->toAugmentedCollection());
    }

    /** @test */
    public function setting_queried_keys_will_filter_the_arrayable_array()
    {
        $entry = (new Entry)
            ->id('test-id')
            ->locale('en')
            ->slug('test')
            ->set('foo', 'bar')
            ->collection(Collection::make('blog')->save());

        $arr = $entry->toAugmentedArray();
        $this->assertTrue(count($arr) > 3);
        $this->assertArraySubset([
            'id' => 'test-id',
            'foo' => 'bar',
            'published' => true,
        ], $arr);

        $return = $entry->selectedQueryColumns(['id', 'foo']);

        $this->assertEquals($entry, $return);
        $this->assertEquals(['id', 'foo'], $entry->selectedQueryColumns());
        $this->assertEquals([
            'id' => 'test-id',
            'foo' => 'bar',
        ], $entry->toAugmentedArray());
    }

    /** @test */
    public function it_converts_to_an_array()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return [
                    new Value('alfa'),
                    new Value([
                        new Value('bravo'),
                        new Value('charlie'),
                        'delta',
                    ]),
                ];
            }
        };
        $fieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'baz' => [
                'type' => 'test',
            ],
        ]);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'post' => $blueprint->setHandle('post'),
        ]));

        $entry = (new Entry)
            ->id('test-id')
            ->locale('en')
            ->slug('test')
            ->set('foo', 'bar')
            ->set('baz', 'qux')
            ->collection(Collection::make('blog')->save());

        $this->assertInstanceOf(Arrayable::class, $entry);

        $array = $entry->toArray();
        $this->assertEquals($entry->augmented()->keys(), array_keys($array));
        $this->assertEquals([
            'alfa',
            [
                'bravo',
                'charlie',
                'delta',
            ],
        ], $array['baz'], 'Value objects are not resolved recursively');

        $array = $entry
            ->selectedQueryColumns($keys = ['id', 'foo', 'baz'])
            ->toArray();

        $this->assertEquals($keys, array_keys($array), 'toArray keys differ from selectedQueryColumns');
    }

    /** @test */
    public function only_requested_relationship_fields_are_included_in_to_array()
    {
        $regularFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'regular';

            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };
        $regularFieldtype::register();

        $relationshipFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'relationship';
            protected $relationship = true;

            public function augment($values)
            {
                return collect($values)->map(fn ($value) => 'augmented '.$value)->all();
            }
        };
        $relationshipFieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'alfa' => ['type' => 'regular'],
            'bravo' => ['type' => 'relationship'],
            'charlie' => ['type' => 'relationship'],
        ]);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'post' => $blueprint->setHandle('post'),
        ]));

        $entry = (new Entry)
            ->id('test-id')
            ->locale('en')
            ->slug('test')
            ->set('alfa', 'one')
            ->set('bravo', ['a', 'b'])
            ->set('charlie', ['c', 'd'])
            ->collection(Collection::make('blog')->save());

        $this->assertEquals([
            'alfa' => 'augmented one',
            'bravo' => ['a', 'b'],
            'charlie' => ['augmented c', 'augmented d'],
        ], Arr::only($entry->selectedQueryRelations(['charlie'])->toArray(), ['alfa', 'bravo', 'charlie']));
    }

    /** @test */
    public function it_gets_and_sets_initial_path()
    {
        $entry = new Entry;
        $this->assertNull($entry->initialPath());

        $return = $entry->initialPath('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->initialPath());
    }

    /** @test */
    public function it_gets_the_path_and_excludes_locale_when_theres_a_single_site()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => '/', 'locale' => 'en_US'],
        ]]);

        $collection = tap(Facades\Collection::make('blog'))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug('post');

        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/post.md', $entry->path());
        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/2018-01-02.post.md', $entry->date('2018-01-02')->path());
    }

    /** @test */
    public function it_gets_the_path_and_includes_locale_when_theres_multiple_sites()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR'],
        ]]);

        $collection = tap(Facades\Collection::make('blog'))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug('post');

        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/en/post.md', $entry->path());
        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/en/2018-01-02.post.md', $entry->date('2018-01-02')->path());
    }

    /** @test */
    public function the_path_uses_the_slug_if_set_even_if_slugs_arent_required()
    {
        $collection = tap(Facades\Collection::make('blog')->requiresSlugs(false))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug('post')->id('123');

        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/post.md', $entry->path());
    }

    /** @test */
    public function the_path_uses_the_id_if_slug_is_not_set()
    {
        $collection = tap(Facades\Collection::make('blog')->requiresSlugs(false))->save();
        $entry = (new Entry)->collection($collection)->locale('en')->slug(null)->id('123');

        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/123.md', $entry->path());
    }

    /** @test */
    public function it_gets_and_sets_the_date()
    {
        Carbon::setTestNow(Carbon::parse('2015-09-24'));

        $collection = tap(Facades\Collection::make('blog'))->save();
        $entry = (new Entry)->collection($collection);

        // Without explicitly having the date set, it falls back to the last modified date (which if there's no file, is Carbon::now())
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2015-09-24 00:00')->eq($entry->date()));
        $this->assertFalse($entry->hasDate());

        // Date can be provided as string without time
        $return = $entry->date('2015-03-05');
        $this->assertEquals($entry, $return);
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2015-03-05 00:00')->eq($entry->date()));
        $this->assertTrue($entry->hasDate());

        // Date can be provided as string with time
        $entry->date('2015-03-05-1325');
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2015-03-05 13:25')->eq($entry->date()));
        $this->assertTrue($entry->hasDate());

        // Date can be provided as carbon instance
        $carbon = Carbon::createFromFormat('Y-m-d H:i', '2018-05-02 17:32');
        $entry->date($carbon);
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue($carbon->eq($entry->date()));
        $this->assertTrue($entry->hasDate());
    }

    /** @test */
    public function it_gets_the_order_from_the_collections_structure()
    {
        $collection = tap(Collection::make('ordered'))->save();

        $one = tap((new Entry)->locale('en')->id('one')->collection($collection))->save();
        $two = tap((new Entry)->locale('en')->id('two')->collection($collection))->save();
        $three = tap((new Entry)->locale('en')->id('three')->collection($collection))->save();
        $four = tap((new Entry)->locale('en')->id('four')->collection($collection))->save();

        $this->assertNull($one->order());
        $this->assertNull($two->order());
        $this->assertNull($three->order());
        $this->assertNull($four->order());

        $collection->structureContents([
            'max_depth' => 3,
        ])->save();
        $collection->structure()->in('en')->tree(
            [
                ['entry' => 'three'],
                ['entry' => 'one', 'children' => [
                    ['entry' => 'four'],
                ]],
                ['entry' => 'two'],
            ]
        )->save();

        $this->assertEquals(2, $one->order());
        $this->assertEquals(4, $two->order());
        $this->assertEquals(1, $three->order());
        $this->assertEquals(3, $four->order());
    }

    /** @test */
    public function it_gets_the_order_from_the_data_if_not_structured()
    {
        $collection = tap(Collection::make('test'))->save();

        $one = tap((new Entry)->locale('en')->id('one')->collection($collection))->save();
        $two = tap((new Entry)->locale('en')->id('two')->collection($collection)->data(['order' => 17]))->save();
        $three = tap((new Entry)->locale('en')->id('three')->collection($collection)->data(['order' => 'potato']))->save();
        $four = tap((new Entry)->locale('en')->id('four')->collection($collection)->data(['order' => 24]))->save();
        $five = tap((new Entry)->locale('fr')->id('five')->collection($collection)->origin('four'))->save();

        $this->assertNull($one->order());
        $this->assertEquals(17, $two->order());
        $this->assertEquals('potato', $three->order());
        $this->assertEquals(24, $four->order());
        $this->assertEquals(24, $five->order());
    }

    /** @test */
    public function it_gets_and_sets_the_date_for_date_collections()
    {
        Carbon::setTestNow(Carbon::parse('2015-09-24'));

        $dateEntry = with('', function () {
            $collection = tap(Collection::make('dated')->dated(true))->save();

            return (new Entry)->collection($collection);
        });

        $numberEntry = with('', function () {
            $collection = tap(Collection::make('ordered')->structureContents(['max_depth' => 1, 'tree' => []]))->save();

            return (new Entry)->collection($collection);
        });

        $dateEntry->date('2017-01-02');

        $this->assertEquals('2017-01-02 12:00am', $dateEntry->date()->format('Y-m-d h:ia'));
        $this->assertTrue($dateEntry->hasDate());
        $this->assertFalse($dateEntry->hasTime());

        $this->assertEquals('2015-09-24 12:00am', $numberEntry->date()->format('Y-m-d h:ia'));
        $this->assertFalse($numberEntry->hasDate());

        $dateEntry->date('2017-01-02-1523');
        $this->assertEquals('2017-01-02 03:23pm', $dateEntry->date()->format('Y-m-d h:ia'));
        $this->assertTrue($dateEntry->hasDate());
        $this->assertTrue($dateEntry->hasTime());
    }

    /** @test */
    public function future_dated_entries_are_private_when_configured_in_the_collection()
    {
        Carbon::setTestNow('2019-01-01');
        $collection = tap(Collection::make('dated')->dated(true)->futureDateBehavior('private'))->save();
        $entry = (new Entry)->collection($collection);

        $this->assertFalse($entry->private());

        $entry->date('2018-01-01');
        $this->assertFalse($entry->private());

        $entry->date('2019-01-02');
        $this->assertTrue($entry->private());
    }

    /** @test */
    public function past_dated_entries_are_private_when_configured_in_the_collection()
    {
        Carbon::setTestNow('2019-01-01');
        $collection = tap(Collection::make('dated')->dated(true)->pastDateBehavior('private'))->save();
        $entry = (new Entry)->collection($collection);

        $this->assertTrue($entry->private());

        $entry->date('2019-01-02');
        $this->assertFalse($entry->private());

        $entry->date('2018-01-02');
        $this->assertTrue($entry->private());
    }

    /** @test */
    public function it_gets_and_sets_the_published_state()
    {
        $entry = new Entry;
        $this->assertTrue($entry->published());

        $return = $entry->published(false);

        $this->assertEquals($entry, $return);
        $this->assertFalse($entry->published());
    }

    /** @test */
    public function it_gets_the_blueprint_when_defined_on_itself()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Collection::make('blog')->save();
        $entry = (new Entry)->collection('blog')->blueprint('second');

        $this->assertSame($second, $entry->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_can_set_a_blueprint_using_an_instance()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Collection::make('blog')->save();
        $entry = (new Entry)->collection('blog')->blueprint($second);

        $this->assertSame($second, $entry->blueprint());
    }

    /** @test */
    public function it_gets_the_blueprint_when_defined_in_a_value()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Collection::make('blog')->save();
        $entry = (new Entry)->collection('blog')->set('blueprint', 'second');

        $this->assertSame($second, $entry->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_blueprint_when_defined_in_an_origin_value()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Collection::make('blog')->save();
        $origin = (new Entry)->collection('blog')->set('blueprint', 'second');
        $entry = (new Entry)->collection('blog')->origin($origin);

        $this->assertSame($second, $entry->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_blueprint_when_defined_in_an_origin_property()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Collection::make('blog')->save();
        $origin = (new Entry)->collection('blog')->blueprint('second');
        $entry = (new Entry)->collection('blog')->origin($origin);

        $this->assertSame($second, $entry->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_default_collection_blueprint_when_undefined()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        $collection = tap(Collection::make('blog'))->save();
        $entry = (new Entry)->collection($collection);

        $this->assertSame($first, $entry->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function the_blueprint_is_blinked_when_getting_and_flushed_when_setting()
    {
        $entry = (new Entry)->collection('blog');
        $collection = Mockery::mock(Collection::make('blog'));
        $collection->shouldReceive('entryBlueprint')->with(null, $entry)->once()->andReturn('the old blueprint');
        $collection->shouldReceive('entryBlueprint')->with('new', $entry)->once()->andReturn('the new blueprint');
        Collection::shouldReceive('findByHandle')->with('blog')->andReturn($collection);

        $this->assertEquals('the old blueprint', $entry->blueprint());
        $this->assertEquals('the old blueprint', $entry->blueprint());

        $entry->blueprint('new');

        $this->assertEquals('the new blueprint', $entry->blueprint());
        $this->assertEquals('the new blueprint', $entry->blueprint());
    }

    /** @test */
    public function it_saves_through_the_api()
    {
        Event::fake();

        $collection = (new Collection)->handle('pages')->save();
        $entry = (new Entry)->id('a')->collection($collection);
        Facades\Entry::shouldReceive('save')->with($entry);
        Facades\Entry::shouldReceive('taxonomize')->with($entry);
        Facades\Entry::shouldReceive('find')->with('a')->once()->andReturnNull();

        $return = $entry->save();

        $this->assertTrue($return);
        Event::assertDispatched(EntrySaving::class, function ($event) use ($entry) {
            return $event->entry === $entry;
        });
        Event::assertDispatched(EntryCreated::class, function ($event) use ($entry) {
            return $event->entry === $entry;
        });
        Event::assertDispatched(EntrySaved::class, function ($event) use ($entry) {
            return $event->entry === $entry;
        });
    }

    /** @test */
    public function it_dispatches_entry_created_only_once()
    {
        Event::fake();

        $collection = (new Collection)->handle('pages')->save();
        $entry = (new Entry)->id('1')->collection($collection);
        Facades\Entry::shouldReceive('save')->with($entry);
        Facades\Entry::shouldReceive('taxonomize')->with($entry);
        Facades\Entry::shouldReceive('find')->with('1')->times(3)->andReturn(null, $entry, $entry);

        $entry->save();
        $entry->save();
        $entry->save();

        Event::assertDispatched(EntrySaved::class, 3);
        Event::assertDispatched(EntryCreated::class, 1);
    }

    /** @test */
    public function it_saves_quietly()
    {
        Event::fake();

        $collection = (new Collection)->handle('pages')->save();
        $entry = (new Entry)->id('a')->collection($collection);
        Facades\Entry::shouldReceive('save')->with($entry);
        Facades\Entry::shouldReceive('taxonomize')->with($entry);
        Facades\Entry::shouldReceive('find')->with('a')->once()->andReturnNull();

        $return = $entry->saveQuietly();

        $this->assertTrue($return);
        Event::assertNotDispatched(EntrySaving::class);
        Event::assertNotDispatched(EntrySaved::class);
        Event::assertNotDispatched(EntryCreated::class);
    }

    /** @test */
    public function when_saving_quietly_the_cached_entrys_withEvents_flag_will_be_set_back_to_true()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.

        $entry = EntryFactory::collection('blog')->id('1')->create();

        $entry->saveQuietly();

        $cached = Cache::get('stache::items::entries::blog::1');
        $reflection = new ReflectionClass($cached);
        $property = $reflection->getProperty('withEvents');
        $property->setAccessible(true);
        $withEvents = $property->getValue($cached);
        $this->assertTrue($withEvents);
    }

    /** @test */
    public function it_clears_blink_caches_when_saving()
    {
        $collection = tap(Collection::make('test')->structure(new CollectionStructure))->save();
        $entry = (new Entry)->id('a')->collection($collection);

        $mock = \Mockery::mock(Facades\Blink::getFacadeRoot())->makePartial();
        Facades\Blink::swap($mock);
        $mock->shouldReceive('store')->with('structure-uris')->once()->andReturn(
            $this->mock(\Spatie\Blink\Blink::class)->shouldReceive('forget')->with('a')->once()->getMock()
        );
        $mock->shouldReceive('store')->with('structure-entries')->once()->andReturn(
            $this->mock(\Spatie\Blink\Blink::class)->shouldReceive('forget')->with('a')->once()->getMock()
        );

        $entry->save();
    }

    /** @test */
    public function it_performs_callbacks_after_saving_but_before_the_saved_event_and_only_once()
    {
        Event::fake();

        $collection = (new Collection)->handle('pages')->save();
        $entry = (new Entry)->id('a')->collection($collection);
        Facades\Entry::shouldReceive('save')->with($entry);
        Facades\Entry::shouldReceive('taxonomize')->with($entry);
        Facades\Entry::shouldReceive('find')->with('a')->times(2)->andReturn(null, $entry);
        $callbackOneRan = 0;
        $callbackTwoRan = 0;

        $return = $entry->afterSave(function ($arg) use (&$callbackOneRan, $entry) {
            $this->assertSame($entry, $arg);
            $arg->set('result', 'one');
            $callbackOneRan++;
        });
        $entry->afterSave(function ($arg) use (&$callbackTwoRan, $entry) {
            $this->assertSame($entry, $arg);
            $arg->set('result', 'two');
            $callbackTwoRan++;
        });

        $entry->save();
        $entry->save(); // save twice to show that the callbacks only get run the first time.

        $this->assertEquals($entry, $return);
        $this->assertEquals(1, $callbackOneRan);
        $this->assertEquals(1, $callbackTwoRan);

        // TODO: How to test that the callbacks are run *before* the EntrySaved event?
    }

    /** @test */
    public function it_propagates_entry_if_configured()
    {
        Event::fake();

        Facades\Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'es' => ['name' => 'Spanish', 'locale' => 'es_ES', 'url' => 'http://test.com/es/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $collection = (new Collection)
            ->handle('pages')
            ->propagate(true)
            ->sites(['en', 'fr', 'de'])
            ->save();

        $entry = (new Entry)
            ->id('a')
            ->locale('en')
            ->collection($collection);

        $return = $entry->save();

        $this->assertIsObject($fr = $entry->descendants()->get('fr'));
        $this->assertIsObject($de = $entry->descendants()->get('de'));
        $this->assertNull($entry->descendants()->get('es')); // collection not configured for this site

        Event::assertDispatchedTimes(EntrySaving::class, 3);
        Event::assertDispatched(EntrySaving::class, function ($event) use ($entry) {
            return $event->entry === $entry;
        });
        Event::assertDispatched(EntrySaving::class, function ($event) use ($fr) {
            return $event->entry === $fr;
        });
        Event::assertDispatched(EntrySaving::class, function ($event) use ($de) {
            return $event->entry === $de;
        });

        Event::assertDispatchedTimes(EntryCreated::class, 3);
        Event::assertDispatched(EntryCreated::class, function ($event) use ($entry) {
            return $event->entry === $entry;
        });
        Event::assertDispatched(EntryCreated::class, function ($event) use ($fr) {
            return $event->entry === $fr;
        });
        Event::assertDispatched(EntryCreated::class, function ($event) use ($de) {
            return $event->entry === $de;
        });

        Event::assertDispatchedTimes(EntrySaved::class, 3);
        Event::assertDispatched(EntrySaved::class, function ($event) use ($entry) {
            return $event->entry === $entry;
        });
        Event::assertDispatched(EntrySaved::class, function ($event) use ($fr) {
            return $event->entry === $fr;
        });
        Event::assertDispatched(EntrySaved::class, function ($event) use ($de) {
            return $event->entry === $de;
        });
    }

    /** @test */
    public function it_propagates_entry_from_non_default_site_if_configured()
    {
        Event::fake();

        Facades\Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $collection = (new Collection)
            ->handle('pages')
            ->propagate(true)
            ->sites(['en', 'fr', 'de'])
            ->save();

        $entry = (new Entry)
            ->id('a')
            ->locale('fr')
            ->collection($collection);

        $return = $entry->save();

        $this->assertIsObject($entry->descendants()->get('en'));
        $this->assertIsObject($entry->descendants()->get('de'));
    }

    /** @test */
    public function it_does_not_propagate_if_not_configured()
    {
        Event::fake();

        Facades\Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $collection = (new Collection)
            ->handle('pages')
            ->sites(['en', 'fr', 'de'])
            ->save();

        $entry = (new Entry)
            ->id('a')
            ->locale('en')
            ->collection($collection);

        $return = $entry->save();

        $this->assertNull($entry->descendants()->get('fr'));
        $this->assertNull($entry->descendants()->get('de'));
    }

    /** @test */
    public function it_does_not_propagate_existing_entries()
    {
        Event::fake();

        Facades\Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'es' => ['name' => 'Spanish', 'locale' => 'es_ES', 'url' => 'http://test.com/es/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $collection = (new Collection)
            ->handle('pages')
            ->propagate(false)
            ->sites(['en', 'fr', 'de'])
            ->save();

        $entry = (new Entry)
            ->id('a')
            ->locale('en')
            ->collection($collection);

        $entry->save();
        $this->assertCount(1, Entry::all());

        $collection->propagate(true)->save();

        $entry->save();
        $this->assertCount(1, Entry::all());
    }

    /** @test */
    public function if_saving_event_returns_false_the_entry_doesnt_save()
    {
        Facades\Entry::spy();
        Event::fake([EntrySaved::class]);

        Event::listen(EntrySaving::class, function () {
            return false;
        });

        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->collection($collection);

        $return = $entry->save();

        $this->assertFalse($return);
        Facades\Entry::shouldNotHaveReceived('save');
        Event::assertNotDispatched(EntrySaved::class);
    }

    /** @test */
    public function it_gets_file_contents_for_saving()
    {
        tap(Collection::make('test'))->save();

        $entry = (new Entry)
            ->collection('test')
            ->id('123')
            ->slug('test')
            ->date('2018-01-01')
            ->published(false)
            ->data([
                'title' => 'The title',
                'array' => ['first one', 'second one'],
                'null' => null,      // this...
                'empty' => [], // and this should get stripped out because it's the root. there's no origin to fall back to.
                'content' => 'The content',
            ]);

        $this->assertEquals([
            'title' => 'The title',
            'array' => [
                'first one',
                'second one',
            ],
            'id' => '123',
            'published' => false,
            'content' => 'The content',
            'blueprint' => 'test',
        ], $entry->fileData());
    }

    /** @test */
    public function it_gets_file_contents_for_saving_a_localized_entry()
    {
        tap(Collection::make('test'))->save();

        $originEntry = $this->mock(Entry::class);
        $originEntry->shouldReceive('id')->andReturn('123');

        Facades\Entry::shouldReceive('find')->with('123')->andReturn($originEntry);
        $originEntry->shouldReceive('values')->andReturn(collect([]));
        $originEntry->shouldReceive('blueprint')->andReturn(
            $this->mock(Blueprint::class)->shouldReceive('handle')->andReturn('test')->getMock()
        );

        $entry = (new Entry)
            ->collection('test')
            ->id('456')
            ->origin('123')
            ->slug('test')
            ->date('2018-01-01')
            ->published(false)
            ->data([
                'title' => 'The title',
                'array' => ['first one', 'second one'],
                'null' => null,      // this...
                'empty' => [], // and this should not get stripped out, otherwise it would fall back to the origin.
                'content' => 'The content',
            ]);

        $this->assertEquals([
            'title' => 'The title',
            'array' => [
                'first one',
                'second one',
            ],
            'null' => null,
            'empty' => [],
            'id' => '456',
            'origin' => '123',
            'published' => false,
            'content' => 'The content',
        ], $entry->fileData());
    }

    /** @test */
    public function the_default_blueprint_is_added_to_the_file_contents_when_one_hasnt_been_explicitly_defined()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'default' => (new Blueprint)->setHandle('default'),
            'another' => (new Blueprint)->setHandle('another'),
        ]));
        $collection = tap(Collection::make('test'))->save();
        $this->assertEquals('default', $collection->entryBlueprint()->handle());

        $entry = (new Entry)->collection('test');

        $this->assertEquals('default', $entry->fileData()['blueprint']);
    }

    /** @test */
    public function the_explicit_blueprint_is_added_to_the_file_contents()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'default' => (new Blueprint)->setHandle('default'),
            'another' => (new Blueprint)->setHandle('another'),
        ]));
        $collection = tap(Collection::make('test'))->save();
        $this->assertEquals('default', $collection->entryBlueprint()->handle());

        $entry = (new Entry)->collection('test')->blueprint('another');

        $this->assertEquals('another', $entry->fileData()['blueprint']);
    }

    /** @test */
    public function the_blueprint_is_not_added_to_the_localized_file_contents()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'default' => (new Blueprint)->setHandle('default'),
            'another' => (new Blueprint)->setHandle('another'),
        ]));
        $collection = tap(Collection::make('test'))->save();
        $this->assertEquals('default', $collection->entryBlueprint()->handle());

        $originEntry = $this->mock(Entry::class);
        $originEntry->shouldReceive('id')->andReturn('123');

        Facades\Entry::shouldReceive('find')->with('123')->andReturn($originEntry);
        $originEntry->shouldReceive('values')->andReturn(collect([]));
        $originEntry->shouldReceive('blueprint')->andReturn(
            $this->mock(Blueprint::class)->shouldReceive('handle')->andReturn('another')->getMock()
        );

        $entry = (new Entry)
            ->collection('test')
            ->origin('123'); // do not set blueprint.

        $this->assertArrayNotHasKey('blueprint', $entry->fileData());
    }

    /** @test */
    public function the_blueprint_is_added_to_the_localized_file_contents_if_explicitly_different_from_the_origin()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'default' => (new Blueprint)->setHandle('default'),
            'another' => (new Blueprint)->setHandle('another'),
        ]));
        $collection = tap(Collection::make('test'))->save();
        $this->assertEquals('default', $collection->entryBlueprint()->handle());

        $originEntry = $this->mock(Entry::class);
        $originEntry->shouldReceive('id')->andReturn('123');

        Facades\Entry::shouldReceive('find')->with('123')->andReturn($originEntry);
        $originEntry->shouldReceive('values')->andReturn(collect([]));
        $originEntry->shouldReceive('blueprint')->andReturn(
            $this->mock(Blueprint::class)->shouldReceive('handle')->andReturn('another')->getMock()
        );

        $entry = (new Entry)
            ->collection('test')
            ->origin('123')
            ->blueprint('default');

        $this->assertEquals('default', $entry->fileData()['blueprint']);
    }

    /** @test */
    public function it_gets_and_sets_the_template()
    {
        $collection = tap(Collection::make('test'))->save();
        $origin = (new Entry)->collection($collection);
        $entry = (new Entry)->collection($collection)->origin($origin);

        // defaults to default
        $this->assertEquals('default', $entry->template());

        // collection level overrides the default
        $collection->template('foo');
        $this->assertEquals('foo', $entry->template());

        // origin overrides collection
        $origin->template('bar');
        $this->assertEquals('bar', $entry->template());

        // entry level overrides the origin
        $return = $entry->template('baz');
        $this->assertEquals($entry, $return);
        $this->assertEquals('baz', $entry->template());
    }

    /** @test */
    public function it_gets_and_sets_an_inferred_template_from_blueprint()
    {
        $collection = tap(Collection::make('articles')->template('@blueprint'))->save();
        $blueprint = tap(Blueprint::make('standard_article')->setNamespace('collections.articles'))->save();
        $entry = Entry::make('test')->collection($collection)->blueprint($blueprint->handle());

        // entry looks for `articles.standard_article` template (snake case) by default
        $this->assertEquals('articles.standard_article', $entry->template());

        // entry uses `articles.standard-article` template (slug case)
        // when user has slug cased template in views folder
        View::shouldReceive('exists')->with('articles.standard-article')->andReturn(true);
        $this->assertEquals('articles.standard-article', $entry->template());

        // entry level template overrides `@blueprint` on the collection
        $entry->template('articles.custom');
        $this->assertEquals('articles.custom', $entry->template());
    }

    /** @test */
    public function it_gets_and_sets_the_layout()
    {
        $collection = tap(Collection::make('test'))->save();
        $origin = (new Entry)->collection($collection);
        $entry = (new Entry)->collection($collection)->origin($origin);

        // defaults to layout
        $this->assertEquals('layout', $entry->layout());

        // collection level overrides the default
        $collection->layout('foo');
        $this->assertEquals('foo', $entry->layout());

        // origin overrides collection
        $origin->layout('bar');
        $this->assertEquals('bar', $entry->layout());

        // entry level overrides the origin
        $return = $entry->layout('baz');
        $this->assertEquals($entry, $return);
        $this->assertEquals('baz', $entry->layout());
    }

    /** @test */
    public function it_gets_the_last_modified_time()
    {
        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->collection($collection)->slug('bar');
        $path = $entry->path();
        $date = Carbon::parse('2017-01-02');
        mkdir(dirname($path));
        touch($path, $date->timestamp);

        $this->assertTrue($date->eq($entry->lastModified()));

        $valueBasedDate = Carbon::parse('2017-01-03');
        $entry->set('updated_at', $valueBasedDate->timestamp);
        $this->assertFalse($date->eq($entry->lastModified()));
        $this->assertTrue($valueBasedDate->eq($entry->lastModified()));

        @unlink($path);
    }

    /** @test */
    public function it_gets_and_sets_the_collection()
    {
        $entry = new Entry;
        $collection = tap(Collection::make('foo'))->save();
        $this->assertNull($entry->collection());

        $return = $entry->collection($collection);

        $this->assertEquals($entry, $return);
        $this->assertEquals($collection, $entry->collection());
        $this->assertEquals('foo', $entry->collectionHandle());
    }

    /** @test */
    public function it_gets_and_sets_the_id()
    {
        $entry = new Entry;
        $this->assertNull($entry->id());

        $return = $entry->id('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->id());
        // $this->assertEquals('entry::123', $entry->reference()); // TODO, implementation works but test needs to be adjusted
    }

    /** @test */
    public function it_deletes_through_the_api()
    {
        Event::fake();
        $entry = (new Entry)->collection(tap(Collection::make('test'))->save());

        $mock = \Mockery::mock(Facades\Entry::getFacadeRoot())->makePartial();
        Facades\Entry::swap($mock);
        $mock->shouldReceive('delete')->with($entry);

        $return = $entry->delete();

        $this->assertTrue($return);
    }

    /** @test */
    public function it_prevents_deleting_if_there_are_descendants()
    {
        $this->expectExceptionMessage('Cannot delete an entry with localizations.');

        $entry = EntryFactory::collection('test')->create();
        EntryFactory::collection('test')->origin($entry->id())->create();

        $entry->delete();
    }

    /** @test */
    public function it_deletes_descendants()
    {
        Event::fake();
        config(['statamic.sites.sites' => [
            'en' => [],
            'fr' => [],
            'de' => [],
        ]]);

        $entry = EntryFactory::collection('test')->locale('en')->id('1')->create();
        $localization = EntryFactory::collection('test')->locale('fr')->id('2')->origin('1')->create();
        $deeperLocalization = EntryFactory::collection('test')->locale('de')->id('3')->origin('2')->create();

        $this->assertCount(3, Facades\Entry::all());
        $this->assertCount(2, $entry->descendants());
        $this->assertCount(1, $localization->descendants());

        $return = $entry->deleteDescendants();

        $this->assertTrue($return);
        $this->assertCount(1, Facades\Entry::all());
        $this->assertCount(0, $entry->descendants());
        $this->assertCount(0, $localization->descendants());
    }

    /** @test */
    public function it_detaches_localizations()
    {
        Event::fake();
        config(['statamic.sites.sites' => [
            'en' => [],
            'fr' => [],
            'fr_ca' => [],
        ]]);

        $english = EntryFactory::collection('test')->locale('en')->id('en')->data([
            'title' => 'English',
            'food' => 'Burger',
            'drink' => 'Water',
        ])->create();

        $french = EntryFactory::collection('test')->locale('fr')->id('fr')->origin('en')->data([
            'title' => 'French',
            'food' => 'Croissant',
        ])->create();

        $frenchCanadian = EntryFactory::collection('test')->locale('fr_ca')->id('fr_ca')->origin('fr')->data([
            'title' => 'French Canadian',
            'food' => 'Poutine',
        ])->create();

        $this->assertEquals('English', $english->value('title'));
        $this->assertEquals('Burger', $english->value('food'));
        $this->assertEquals('Water', $english->value('drink'));
        $this->assertEquals([
            'title' => 'English',
            'food' => 'Burger',
            'drink' => 'Water',
        ], $english->data()->all());

        $this->assertEquals($english, $french->origin());
        $this->assertEquals('French', $french->value('title'));
        $this->assertEquals('Croissant', $french->value('food'));
        $this->assertEquals('Water', $french->value('drink'));
        $this->assertEquals([
            'title' => 'French',
            'food' => 'Croissant',
        ], $french->data()->all());

        $this->assertEquals($french, $frenchCanadian->origin());
        $this->assertEquals('French Canadian', $frenchCanadian->value('title'));
        $this->assertEquals('Poutine', $frenchCanadian->value('food'));
        $this->assertEquals('Water', $frenchCanadian->value('drink'));
        $this->assertEquals([
            'title' => 'French Canadian',
            'food' => 'Poutine',
        ], $frenchCanadian->data()->all());

        $return = $english->detachLocalizations();

        $this->assertTrue($return);
        $this->assertEquals('English', $english->value('title'));
        $this->assertEquals('Burger', $english->value('food'));
        $this->assertEquals('Water', $english->value('drink'));
        $this->assertEquals([
            'title' => 'English',
            'food' => 'Burger',
            'drink' => 'Water',
        ], $english->data()->all());

        $this->assertNull($french->origin());
        $this->assertEquals('French', $french->value('title'));
        $this->assertEquals('Croissant', $french->value('food'));
        $this->assertEquals('Water', $french->value('drink'));
        $this->assertEquals([
            'title' => 'French',
            'food' => 'Croissant',
            'drink' => 'Water',
        ], $french->data()->all());

        $this->assertEquals($french, $frenchCanadian->origin());
        $this->assertEquals('French Canadian', $frenchCanadian->value('title'));
        $this->assertEquals('Poutine', $frenchCanadian->value('food'));
        $this->assertEquals('Water', $frenchCanadian->value('drink'));
        $this->assertEquals([
            'title' => 'French Canadian',
            'food' => 'Poutine',
        ], $frenchCanadian->data()->all());
    }

    /** @test */
    public function it_gets_the_corresponding_page_from_the_collections_structure()
    {
        $parentPage = $this->mock(Page::class);
        $page = $this->mock(Page::class);
        $page->shouldReceive('parent')->andReturn($parentPage);
        $tree = $this->partialMock(CollectionTree::class);
        $tree->locale('en');
        $tree->shouldReceive('page')->with('entry-id')->andReturn($page);
        CollectionTreeRepository::shouldReceive('find', 'en')->andReturn($tree);

        $structure = new CollectionStructure;
        $collection = tap(Collection::make('test')->structure($structure))->save();

        $entry = (new Entry)->id('entry-id')->locale('en')->collection($collection);

        $this->assertSame($page, $entry->page());
        $this->assertSame($parentPage, $entry->parent());
    }

    /** @test */
    public function no_page_is_returned_when_the_collection_isnt_using_a_structure()
    {
        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->id('entry-id')->locale('en')->collection($collection);

        $this->assertNull($entry->page());
        $this->assertNull($entry->parent());
    }

    /**
     * @test
     * @dataProvider autoGeneratedTitleProvider
     */
    public function it_gets_the_auto_generated_title($format)
    {
        $other = EntryFactory::collection('products')
            ->slug('talkboy')
            ->data(['name' => 'Talkboy', 'company' => 'Tiger Electronics'])
            ->create();

        $collection = tap(Collection::make('test')->titleFormats($format))->save();

        $blueprint = Blueprint::makeFromFields([
            'products' => ['type' => 'entries'],
        ]);
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'product' => $blueprint->setHandle('product'),
        ]));

        $entry = (new Entry)->id('entry-id')->locale('en')->collection($collection)->data([
            'product' => 'Talkboy',
            'company' => 'Tiger Electronics',
            'products' => [$other->id()],
        ]);

        $this->assertEquals('Talkboy by Tiger Electronics', $entry->autoGeneratedTitle());
    }

    public function autoGeneratedTitleProvider()
    {
        return [
            'antlers' => ['{{ product }} by {{ company }}'],
            'mustache' => ['{product} by {company}'],
            'antlers nested' => ['{{ products:0:name }} by {{ products:0:company }}'],
            'mustache nested with colons' => ['{products:0:name} by {products:0:company}'],
            'mustache nested with dots' => ['{products.0.name} by {products.0.company}'],
        ];
    }

    /** @test */
    public function it_gets_preview_targets()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr_FR'],
            'de' => ['url' => 'http://domain.de/', 'locale' => 'de_DE'],
        ]]);

        $collection = (new Collection)->sites(['en', 'fr', 'de'])->handle('blog')->ampable(true)->routes([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
            'de' => 'das-blog/{slug}',
        ]);
        $collection->save();

        $entryEn = (new Entry)->collection($collection)->locale('en')->slug('foo');
        $entryFr = (new Entry)->collection($collection)->locale('fr')->slug('le-foo');
        $entryDe = (new Entry)->collection($collection)->locale('de')->slug('das-foo');

        $this->assertEquals([
            ['label' => 'Entry', 'format' => '{permalink}', 'url' => 'http://domain.com/blog/foo'],
        ], $entryEn->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Entry', 'format' => '{permalink}', 'url' => 'http://domain.com/fr/le-blog/le-foo'],
        ], $entryFr->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Entry', 'format' => '{permalink}', 'url' => 'http://domain.de/das-blog/das-foo'],
        ], $entryDe->previewTargets()->all());

        $collection->previewTargets([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/blog?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/blog/{slug}?preview=true'],
        ])->save();

        $this->assertEquals([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/blog?preview=true', 'url' => 'http://preview.com/en/blog?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/blog/{slug}?preview=true', 'url' => 'http://preview.com/en/blog/foo?preview=true'],
        ], $entryEn->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/blog?preview=true', 'url' => 'http://preview.com/fr/blog?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/blog/{slug}?preview=true', 'url' => 'http://preview.com/fr/blog/le-foo?preview=true'],
        ], $entryFr->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/blog?preview=true', 'url' => 'http://preview.com/de/blog?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/blog/{slug}?preview=true', 'url' => 'http://preview.com/de/blog/das-foo?preview=true'],
        ], $entryDe->previewTargets()->all());
    }

    // todo: add tests for localization things. in(), descendants(), addLocalization(), etc
}
