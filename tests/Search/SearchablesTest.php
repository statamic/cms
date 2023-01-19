<?php

namespace Tests\Search;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Assert;
use Statamic\Assets\AssetCollection;
use Statamic\Auth\UserCollection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Facades\Search;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Search\Searchables;
use Statamic\Search\Searchables\Provider;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Stache\Query\TermQueryBuilder;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SearchablesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->save();

        Storage::fake('images');
        Storage::fake('documents');
        Storage::fake('audio');
        AssetContainer::make('images')->disk('images')->save();
        AssetContainer::make('documents')->disk('documents')->save();
        AssetContainer::make('audio')->disk('audio')->save();
    }

    /** @test */
    public function all_searchables_include_entries_terms_assets_and_users()
    {
        $entries = [$entryA = Entry::make(), $entryB = Entry::make(), $entryC = Entry::make()->published(false)];
        Entry::shouldReceive('all')->once()->andReturn(EntryCollection::make($entries));
        $terms = [$termA = Term::make(), $termB = Term::make()];
        Term::shouldReceive('all')->once()->andReturn(TermCollection::make($terms));
        $assets = [$assetA = Asset::make(), $assetB = Asset::make()];
        Asset::shouldReceive('all')->once()->andReturn(AssetCollection::make($assets));
        $users = [$userA = User::make(), $userB = User::make()];
        User::shouldReceive('all')->once()->andReturn(UserCollection::make($users));

        $searchables = $this->makeSearchables(['searchables' => 'all']);

        $everything = [
            $entryA,
            $entryB,
            $termA,
            $termB,
            $assetA,
            $assetB,
            $userA,
            $userB,
        ];

        $items = $searchables->all();
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals($everything, $items->all());

        foreach ($everything as $searchable) {
            $this->assertTrue($searchables->contains($searchable));
        }

        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_entries_in_specific_collections()
    {
        $blog = [$entryA = Entry::make()->collection('blog'), $entryB = Entry::make()->collection('blog'), $entryC = Entry::make()->collection('blog')->published(false)];
        $pages = [$entryC = Entry::make()->collection('pages'), $entryD = Entry::make()->collection('pages')];
        $entry = Entry::make()->collection('events');
        $term = Term::make();
        $asset = Asset::make();
        $user = User::make();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        $entryQuery = Mockery::mock(EntryQueryBuilder::class);
        $entryQuery->shouldReceive('get')->andReturn(EntryCollection::make(array_merge($blog, $pages)));
        Entry::shouldReceive('query->whereIn')->with('collection', ['blog', 'pages'])->once()->andReturn($entryQuery);

        $searchables = $this->makeSearchables(['searchables' => ['collection:blog', 'collection:pages']]);

        $expected = [$entryA, $entryB, $entryC, $entryD];
        $this->assertEquals($expected, $searchables->all()->all());
        foreach ($expected as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_entries_in_all_collections()
    {
        $entries = [
            $entryA = Entry::make()->collection('blog'),
            $entryB = Entry::make()->collection('blog')->published(false),
            $entryC = Entry::make()->collection('pages'),
        ];
        $term = Term::make();
        $asset = Asset::make();
        $user = User::make();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        Entry::shouldReceive('all')->once()->andReturn(EntryCollection::make($entries));

        $searchables = $this->makeSearchables(['searchables' => ['collection:*']]);

        $this->assertEquals([$entryA, $entryC], $searchables->all()->all());
        $this->assertTrue($searchables->contains($entryA));
        $this->assertFalse($searchables->contains($entryB));
        $this->assertTrue($searchables->contains($entryC));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_terms_in_specific_taxonomies()
    {
        $tags = [$termA = Term::make()->taxonomy('tags'), $termB = Term::make()->taxonomy('tags')];
        $categories = [$termC = Term::make()->taxonomy('categories'), $termD = Term::make()->taxonomy('categories')];
        $entry = Entry::make();
        $term = Term::make();
        $asset = Asset::make();
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        $termQuery = Mockery::mock(TermQueryBuilder::class);
        $termQuery->shouldReceive('get')->andReturn(TermCollection::make(array_merge($tags, $categories)));
        Term::shouldReceive('query->whereIn')->with('taxonomy', ['tags', 'categories'])->once()->andReturn($termQuery);

        $searchables = $this->makeSearchables(['searchables' => ['taxonomy:tags', 'taxonomy:categories']]);

        $expected = [$termA, $termB, $termC, $termD];
        $this->assertEquals($expected, $searchables->all()->all());
        foreach ($expected as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_terms_in_all_taxonomies()
    {
        $terms = [new LocalizedTerm(Term::make(), 'en'), new LocalizedTerm(Term::make(), 'en')];
        $entry = Entry::make();
        $asset = Asset::make();
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        Term::shouldReceive('all')->once()->andReturn(TermCollection::make($terms));

        $searchables = $this->makeSearchables(['searchables' => ['taxonomy:*']]);

        $this->assertEquals($terms, $searchables->all()->all());
        foreach ($terms as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_assets_in_specific_containers()
    {
        $images = [
            $assetA = Asset::make()->container('images')->path('a.jpg'),
            $assetB = Asset::make()->container('images')->path('b.jpg'),
        ];
        $documents = [
            $assetC = Asset::make()->container('documents')->path('c.jpg'),
            $assetD = Asset::make()->container('documents')->path('d.jpg'),
        ];
        $entry = Entry::make();
        $term = Term::make();
        $asset = Asset::make()->container('audio');
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        User::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->with('images')->once()->andReturn(AssetCollection::make($images));
        Asset::shouldReceive('whereContainer')->with('documents')->once()->andReturn(AssetCollection::make($documents));

        $searchables = $this->makeSearchables(['searchables' => ['assets:images', 'assets:documents']]);

        $expected = [$assetA, $assetB, $assetC, $assetD];
        $this->assertEquals($expected, $searchables->all()->all());
        foreach ($expected as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_assets_in_all_containers()
    {
        $assets = [Asset::make(), Asset::make()];
        $entry = Entry::make();
        $term = Term::make();
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        User::shouldReceive('all')->never();

        Asset::shouldReceive('all')->once()->andReturn(AssetCollection::make($assets));

        $searchables = $this->makeSearchables(['searchables' => ['assets:*']]);
        $this->assertEquals($assets, $searchables->all()->all());
        foreach ($assets as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_users()
    {
        $users = [User::make(), User::make()];
        $entry = Entry::make();
        $term = Term::make();
        $user = User::make();
        $asset = Asset::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();

        User::shouldReceive('all')->once()->andReturn(UserCollection::make($users));

        $searchables = $this->makeSearchables(['searchables' => ['users']]);
        $this->assertEquals($users, $searchables->all()->all());
        foreach ($users as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_transforms_values_set_in_the_config_file()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value, $searchable) {
                    $this->assertEquals('Hello', $value);
                    $this->assertInstanceOf(EntryContract::class, $searchable);
                    $this->assertEquals('a', $searchable->id());

                    return strtoupper($value);
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->id('a')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function it_uses_regular_value_if_theres_not_a_corresponding_transformer()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => ['title'],
            'transformers' => [],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function it_transforms_by_a_class_set_in_the_config_file()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => BasicTestTransformer::class,
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->id('a')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function if_transformed_value_is_a_string_without_a_matching_class_it_throws_exception()
    {
        $this->expectExceptionMessage('Search transformer [foo] not found.');

        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => 'foo',
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);
        $searchables->fields($searchable);
    }

    /** @test */
    public function if_a_closure_based_transformer_returns_an_array_it_gets_combined_into_the_results()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value) {
                    return [
                        'title' => $value,
                        'title_upper' => strtoupper($value),
                    ];
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function if_a_class_based_transformer_returns_an_array_it_gets_combined_into_the_results()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => ArrayTestTransformer::class,
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function can_register_a_custom_searchable_and_get_results()
    {
        $a = new TestCustomSearchable(['title' => 'Custom 1']);
        $b = new TestCustomSearchable(['title' => 'Custom 2']);
        $c = new NotSearchable;
        app()->instance('all-custom-searchables', collect([$a, $b]));

        Search::registerSearchableProvider('custom', new TestCustomSearchables);

        $searchables = $this->makeSearchables(['searchables' => ['custom']]);

        $this->assertEquals([$a, $b], $searchables->all()->all());
        $this->assertTrue($searchables->contains($a));
        $this->assertTrue($searchables->contains($b));
        $this->assertFalse($searchables->contains($c));
    }

    /** @test */
    public function it_throws_exception_when_using_unknown_searchable()
    {
        $this->expectExceptionMessage('Unknown searchable [test]');
        $this->makeSearchables(['searchables' => ['test']]);
    }

    /**
     * @test
     *
     * @dataProvider indexFilterProvider
     */
    public function indexes_can_use_a_custom_filter($filter)
    {
        $entries = EntryCollection::make([
            $entryA = Entry::make()->collection('blog'),
            $entryB = Entry::make()->collection('blog')->published(false),
            $entryC = Entry::make()->collection('blog')->data(['is_searchable' => false]),
            $entryD = Entry::make()->collection('blog')->data(['is_searchable' => true]),
            $entryE = Entry::make()->collection('blog'),
        ]);
        Entry::shouldReceive('all')->andReturn($entries);

        $terms = [
            $termA = Term::make()->taxonomy('tags'),
            $termB = Term::make()->taxonomy('tags')->dataForLocale('en', ['is_searchable' => false]),
            $termC = Term::make()->taxonomy('tags')->dataForLocale('en', ['is_searchable' => true]),
            $termD = Term::make()->taxonomy('tags'),
        ];
        Term::shouldReceive('all')->once()->andReturn(TermCollection::make($terms));

        $assets = [
            $assetA = Asset::make()->container('images'),
            $assetB = Asset::make()->container('images')->set('is_searchable', false),
            $assetC = Asset::make()->container('images')->set('is_searchable', true),
            $assetD = Asset::make()->container('images'),
        ];
        Asset::shouldReceive('all')->once()->andReturn(AssetCollection::make($assets));

        $users = [
            $userA = User::make(),
            $userB = User::make()->set('is_searchable', false),
            $userC = User::make()->set('is_searchable', true),
            $userD = User::make(),
        ];
        User::shouldReceive('all')->once()->andReturn(UserCollection::make($users));

        $searchables = $this->makeSearchables([
            'searchables' => 'all',
            'filter' => $filter,
        ]);

        $this->assertEquals([
            $entryA, $entryB, $entryD, $entryE,
            $termA, $termC, $termD,
            $assetA, $assetC, $assetD,
            $userA, $userC, $userD,
        ], $searchables->all()->all());

        $this->assertTrue($searchables->contains($entryA));
        $this->assertTrue($searchables->contains($entryB));
        $this->assertFalse($searchables->contains($entryC));
        $this->assertTrue($searchables->contains($entryD));
        $this->assertTrue($searchables->contains($entryE));
        $this->assertTrue($searchables->contains($termA));
        $this->assertFalse($searchables->contains($termB));
        $this->assertTrue($searchables->contains($termC));
        $this->assertTrue($searchables->contains($termD));
        $this->assertTrue($searchables->contains($assetA));
        $this->assertFalse($searchables->contains($assetB));
        $this->assertTrue($searchables->contains($assetC));
        $this->assertTrue($searchables->contains($assetD));
        $this->assertTrue($searchables->contains($userA));
        $this->assertFalse($searchables->contains($userB));
        $this->assertTrue($searchables->contains($userC));
        $this->assertTrue($searchables->contains($userD));
    }

    public function indexFilterProvider()
    {
        return [
            'class' => [TestSearchableFilter::class],
            'closure' => [
                function ($entry) {
                    return $entry->get('is_searchable') !== false;
                },
            ],
        ];
    }

    private function makeSearchables($config)
    {
        $index = $this->mock(\Statamic\Search\Index::class);

        $index->shouldReceive('config')->andReturn($config);

        return new Searchables($index);
    }
}

class NotSearchable
{
    //
}

class BasicTestTransformer
{
    public function handle($value, $field, $searchable)
    {
        Assert::assertEquals('Hello', $value);
        Assert::assertEquals('title', $field);
        Assert::assertInstanceOf(EntryContract::class, $searchable);
        Assert::assertEquals('a', $searchable->id());

        return strtoupper($value);
    }
}

class ArrayTestTransformer
{
    public function handle($value, $field)
    {
        return [
            $field => $value,
            $field.'_upper' => strtoupper($value),
        ];
    }
}

class TestSearchableFilter
{
    public function handle($item)
    {
        return $item->get('is_searchable') !== false;
    }
}

class TestCustomSearchable
{
    //
}

class TestCustomSearchables extends Provider
{
    public function find(array $keys): Collection
    {
    }

    public function referencePrefix(): string
    {
        return 'customprefix';
    }

    public function provide(): Collection
    {
        return app('all-custom-searchables');
    }

    public function contains($searchable): bool
    {
        return $searchable instanceof TestCustomSearchable;
    }
}
